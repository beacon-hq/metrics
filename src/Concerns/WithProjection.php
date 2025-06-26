<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

use Beacon\Metrics\Enums\Interval;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

trait WithProjection
{
    protected float|int|null $projectionTargetValue = null;

    protected ?CarbonInterface $projectionTargetDate = null;

    protected array $projections = [];

    public function projectWhen(float|int $targetValue): self
    {
        $this->projectionTargetValue = $targetValue;

        return $this;
    }

    public function projectForDate(CarbonInterface $date): self
    {
        $this->projectionTargetDate = $date;

        return $this;
    }

    protected function calculateProjections(Collection $trendsData): void
    {
        if ($this->projectionTargetValue !== null) {
            if ($trendsData->first() instanceof Collection) {
                $this->projections['when'] = $trendsData->map(function (Collection $groupTrend) {
                    return $this->calculateProjectionToValue($groupTrend, $this->projectionTargetValue);
                });
            } else {
                $this->projections['when'] = $this->calculateProjectionToValue($trendsData, $this->projectionTargetValue);
            }
        }

        if ($this->projectionTargetDate !== null) {
            if ($trendsData->first() instanceof Collection) {
                $this->projections['date'] = $trendsData->map(function (Collection $groupTrend) {
                    return $this->calculateProjectionToDate($groupTrend, $this->projectionTargetDate);
                });
            } else {
                // Store the projection in the result
                $this->projections['date'] = $this->calculateProjectionToDate($trendsData, $this->projectionTargetDate);
            }
        }
    }

    protected function calculateProjectionToValue(Collection $trend, float|int $targetValue): array
    {
        $labels = $trend['labels'];
        $data = $trend['data'];

        if (count($data) < 2) {
            return [
                'target_value' => $targetValue,
                'projected_date' => null,
                'confidence' => 0,
            ];
        }

        $rateOfChange = $this->calculateRateOfChange($data);

        if ((int) $rateOfChange === 0) {
            return [
                'target_value' => $targetValue,
                'projected_date' => null,
                'confidence' => 0,
            ];
        }

        $currentValue = end($data);
        $valueChange = $targetValue - $currentValue;
        $intervalsToTarget = $valueChange / $rateOfChange;

        if ($intervalsToTarget < 0) {
            return [
                'target_value' => $targetValue,
                'projected_date' => null,
                'confidence' => 0,
            ];
        }

        $lastDate = CarbonImmutable::parse(end($labels));
        $projectedDate = $this->addIntervals($lastDate, $intervalsToTarget);

        $confidence = $this->calculateConfidence($data);

        return [
            'target_value' => $targetValue,
            'projected_date' => $projectedDate->toDateTimeString(),
            'confidence' => $confidence,
        ];
    }

    protected function calculateProjectionToDate(Collection $trend, CarbonInterface $targetDate): array
    {
        $labels = $trend['labels'];
        $data = $trend['data'];

        if (count($data) < 2) {
            return [
                'target_date' => $targetDate->toDateTimeString(),
                'projected_value' => null,
                'confidence' => 0,
            ];
        }

        $rateOfChange = $this->calculateRateOfChange($data);

        $lastDate = CarbonImmutable::parse(end($labels));
        $intervalsBetween = $this->calculateIntervalsBetween($lastDate, $targetDate);

        $currentValue = array_sum($data);
        $projectedValue = $currentValue + ($rateOfChange * $intervalsBetween);

        $confidence = $this->calculateConfidence($data);

        return [
            'target_date' => $targetDate->toDateTimeString(),
            'projected_value' => $projectedValue,
            'confidence' => $confidence,
        ];
    }

    protected function calculateRateOfChange(array $data): float
    {
        $changes = [];
        $weights = [];
        $totalWeight = 0;

        for ($i = 1; $i < count($data); $i++) {
            $change = $data[$i] - $data[$i - 1];
            $weight = $i; // Weight increases with recency
            $changes[] = $change;
            $weights[] = $weight;
            $totalWeight += $weight;
        }

        if (empty($changes)) {
            return 0;
        }

        $weightedSum = 0;
        for ($i = 0; $i < count($changes); $i++) {
            $weightedSum += $changes[$i] * $weights[$i];
        }

        $rate = $weightedSum / $totalWeight;

        // Ensure rate is not negative
        return max(0, $rate);
    }

    protected function calculateIntervalsBetween(CarbonInterface $from, CarbonInterface $to): float
    {
        $diff = $from->diff($to);

        return match ($this->interval) {
            Interval::SECOND => $diff->s + ($diff->i * 60) + ($diff->h * 3600) + ($diff->d * 86400),
            Interval::MINUTE => $diff->i + ($diff->h * 60) + ($diff->d * 1440),
            Interval::HOUR => $diff->h + ($diff->d * 24),
            Interval::DAY, Interval::DAY_OF_WEEK => $diff->d,
            Interval::WEEK => $diff->d / 7,
            Interval::MONTH => $diff->m + ($diff->y * 12),
            Interval::YEAR => $diff->y,
        };
    }

    protected function addIntervals(CarbonInterface $date, float $intervals): CarbonInterface
    {
        $wholeIntervals = floor($intervals);
        $partialInterval = $intervals - $wholeIntervals;

        $newDate = match ($this->interval) {
            Interval::SECOND => $date->addSeconds((int) $wholeIntervals),
            Interval::MINUTE => $date->addMinutes((int) $wholeIntervals),
            Interval::HOUR => $date->addHours((int) $wholeIntervals),
            Interval::DAY, Interval::DAY_OF_WEEK => $date->addDays((int) $wholeIntervals),
            Interval::WEEK => $date->addWeeks((int) $wholeIntervals),
            Interval::MONTH => $date->addMonths((int) $wholeIntervals),
            Interval::YEAR => $date->addYears((int) $wholeIntervals),
        };

        if ($partialInterval > 0) {
            $secondsInInterval = match ($this->interval) {
                Interval::SECOND => 1,
                Interval::MINUTE => 60,
                Interval::HOUR => 3600,
                Interval::DAY, Interval::DAY_OF_WEEK => 86400,
                Interval::WEEK => 604800, // 7 days
                Interval::MONTH => 2592000, // 30 days (approximate)
                Interval::YEAR => 31536000, // 365 days (approximate)
            };

            $newDate = $newDate->addSeconds((int) ($partialInterval * $secondsInInterval));
        }

        return $newDate;
    }

    protected function calculateConfidence(array $data): int
    {
        $dataPointsFactor = min(count($data) / 10, 1);

        $changes = [];
        for ($i = 1; $i < count($data); $i++) {
            $changes[] = $data[$i] - $data[$i - 1];
        }

        $mean = array_sum($changes) / count($changes);
        $variance = 0;

        foreach ($changes as $change) {
            $variance += pow($change - $mean, 2);
        }

        $variance = $variance / count($changes);
        $stdDev = sqrt($variance);

        $consistencyFactor = $mean !== 0 ? min(1, 1 / (1 + abs($stdDev / $mean))) : 0;

        return (int) (($dataPointsFactor * 0.4 + $consistencyFactor * 0.6) * 100);
    }
}
