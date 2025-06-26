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
                    return $this->calculateProjectedDateForValue($groupTrend, $this->projectionTargetValue);
                });
            } else {
                $this->projections['when'] = $this->calculateProjectedDateForValue($trendsData, $this->projectionTargetValue);
            }
        }

        if ($this->projectionTargetDate !== null) {
            if ($trendsData->first() instanceof Collection) {
                $this->projections['date'] = $trendsData->map(function (Collection $groupTrend) {
                    $currentTotal = array_sum($groupTrend['data']);

                    return $this->calculateProjectedTotalForDate($groupTrend, $currentTotal, $this->projectionTargetDate);
                });
            } else {
                $currentTotal = array_sum($trendsData['data']);
                $this->projections['date'] = $this->calculateProjectedTotalForDate($trendsData, $currentTotal, $this->projectionTargetDate);
            }
        }
    }

    protected function calculateProjectedDateForValue(Collection $trend, float|int $targetValue): array
    {
        $labels = $trend['labels'];
        $data = $trend['data'];

        if (count($labels) < 2 || count($labels) !== count($data)) {
            return [
                'target_value' => $targetValue,
                'projected_date' => null,
                'confidence' => 0,
            ];
        }

        $normalizedData = $this->normalizeData($labels, $data);

        $currentTotal = array_sum($normalizedData);
        if ($currentTotal >= $targetValue) {
            return [
                'target_value' => $targetValue,
                'projected_date' => null,
                'confidence' => 100,
            ];
        }

        $averageIncrement = $currentTotal / count($normalizedData);

        $intervals = ($targetValue - $currentTotal) / $averageIncrement;

        $lastDate = CarbonImmutable::parse(end($labels));
        $projectedDate = $this->addIntervals($lastDate, $intervals);
        $confidence = $this->calculateConfidence($normalizedData);

        return [
            'target_value' => $targetValue,
            'projected_date' => $projectedDate->toDateTimeString(),
            'confidence' => $confidence,
        ];
    }

    protected function calculateProjectedTotalForDate(Collection $trend, int|float $currentTotal, CarbonInterface $targetDate): array
    {
        $labels = $trend['labels'];
        $data = $trend['data'];

        if (count($labels) < 2 || count($labels) !== count($data)) {
            return [
                'target_date' => $targetDate->toDateTimeString(),
                'projected_total' => null,
                'confidence' => 0,
            ];
        }

        $normalizedData = $this->normalizeData($labels, $data);

        $sum = array_sum($normalizedData);
        $count = count($normalizedData);
        $averageIncrement = $sum / $count;

        $lastDate = CarbonImmutable::parse(end($labels));
        $intervalsToTarget = $this->calculateIntervalsBetween($lastDate, $targetDate);

        $projectedIncrement = 0;
        $minGrowth = 0.1;
        for ($i = 1; $i <= $intervalsToTarget; $i++) {
            $decay = 1 / (1 + $i / 30);
            $increment = max($averageIncrement * $decay, $minGrowth);
            $projectedIncrement += $increment;
        }

        $confidence = $this->calculateConfidence($normalizedData);

        return [
            'target_date' => $targetDate->toDateTimeString(),
            'projected_total' => round($currentTotal + $projectedIncrement),
            'confidence' => $confidence,
        ];
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
        $wholeIntervals = ceil($intervals);
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
                Interval::WEEK => 604800,
                Interval::MONTH => 2592000,
                Interval::YEAR => 31536000,
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

    protected function normalizeData($labels, $data): array
    {
        $start = CarbonImmutable::parse($labels[0]);
        $end = CarbonImmutable::parse($labels[count($labels) - 1]);

        $normalizedData = [];
        $dataByDate = array_combine($labels, $data);
        $date = $start->copy();

        while ($date->lte($end)) {
            $dateStr = $date->format($this->getLabelTimeFormat($this->interval));
            $normalizedData[] = $dataByDate[$dateStr] ?? 0;
            $date = match ($this->interval) {
                Interval::SECOND => $date->addSecond(),
                Interval::MINUTE => $date->addMinute(),
                Interval::HOUR => $date->addHour(),
                Interval::DAY, Interval::DAY_OF_WEEK => $date->addDay(),
                Interval::WEEK => $date->addWeek(),
                Interval::MONTH => $date->addMonth(),
                Interval::YEAR => $date->addYear(),
            };
        }

        return $normalizedData;
    }
}
