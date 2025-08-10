<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

use Beacon\Metrics\Enums\Interval;
use Beacon\Metrics\Values\Collections\TrendMetricCollection;
use Beacon\Metrics\Values\Projections;
use Beacon\Metrics\Values\TrendMetric;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait WithTrendMetric
{
    public function trends(bool $inPercent = false): TrendMetric|TrendMetricCollection
    {
        // @phpstan-ignore method.notFound
        $baseQuery = $this->builder->getConnection()->query()
            ->select(DB::raw($this->formatDatetimeFloor($this->between['from']).' AS dt'));

        // @phpstan-ignore method.notFound
        $recursiveQuery = $this->builder->getConnection()->query()->select(DB::raw($this->getIntervalSequence('date_series.dt').' AS dt'))
            ->where(
                DB::raw($this->getIntervalSequence('date_series.dt')),
                '<=',
                $this->driver !== 'sqlite'
                    ? DB::raw($this->formatDatetimeCeil($this->between['to']))
                    : $this->applyBetween(
                        (clone $this->builder)
                            ->select(DB::raw($this->formatDatetimeCeil($this->between['to'])))
                            ->from($this->table)
                    )
            )
            ->from('date_series');

        $builder = $baseQuery->union($recursiveQuery, $this->driver === 'sqlite');

        if ($this->builder instanceof \Illuminate\Database\Eloquent\Builder) {
            // @phpstan-ignore method.notFound
            $query = (clone $this->builder)
                ->toBase()
                ->withRecursiveExpression('date_series', $builder, ['dt']);
        } else {
            // @phpstan-ignore method.notFound
            $query = (clone $this->builder)
                ->withRecursiveExpression('date_series', $builder, ['dt']);
        }

        $columns = [
            $this->selectData(),
            $this->getIntervalLabel($this->interval, 'date_series.dt'),
            $this->getIntervalGroup($this->interval, 'date_series.dt'),
        ];

        if ($this->groupBy !== null) {
            $columns[] = $this->groupBy;
        }

        $query->select($columns);

        $query->from('date_series');

        $query->leftJoin($this->table, function ($join) {
            $join->whereRaw($this->formatDateTime().' >= date_series.dt')
                ->whereRaw($this->formatDatetime().' < '.$this->getIntervalSequence('date_series.dt'));
        });

        $query = $this->applyBetween($query, false)
            ->when($this->fillMissing, fn (Builder|QueryBuilder $query) => $query->orWhereNull($this->column));

        $query
            ->when($this->groupBy !== null, fn (Builder|QueryBuilder $query) => $query->groupBy('grp')->orderBy('grp'))
            ->groupBy(['grp_label', 'label'])
            ->orderBy('label');

        // We need to ensure the metrics join is first so the original table is available for later joins
        if (count($query->joins) > 1) {
            $joins = collect($query->joins);
            $query->joins = $joins
                ->unshift($joins->last())
                ->slice(0, -1)
                ->toArray();
        }

        $this->query = $query->toRawSql();

        $results = $query->get();
        $trendsData = $results->map(fn (object $datum) => (array) $datum);

        $formattedData = $this->getFormattedTrendsData($trendsData, $inPercent);

        // Include projections if any
        $withProjections = $this->includeProjections($formattedData);

        if ($this->groupBy !== null) {
            return TrendMetricCollection::wrap($withProjections->mapWithKeys(
                function (Collection $groupTrend, string $key): array {
                    return [$key => TrendMetric::from($groupTrend)];
                }
            ));
        }

        return TrendMetric::from(...$withProjections);
    }

    protected function formatTrends(Collection $data, bool $inPercent = false): Collection
    {
        /** @var array{total: int|float, labels: string[], data: int[]|float[]} $result */
        $result = [
            'labels' => [],
            'data' => [],
        ];

        $total = (clone $this);
        if ($this->interval === Interval::WEEK) {
            $total->between(
                $this->between['from']->startOfWeek(),
                $this->between['to']->endOfWeek()
            );
        }

        if ($this->groupBy !== null) {
            $totalValues = $total->value();
            $groupValue = $totalValues->get($data->first()['grp']);
            $result['total'] = $groupValue ? $groupValue->value : 0;
        } else {
            $result['total'] = $total->value()->value;
        }

        $data->each(function ($datum) use ($inPercent, &$result) {
            $result['labels'][] = match ($this->interval) {
                Interval::WEEK => $this->formatWeek($datum['label']),
                Interval::DAY_OF_WEEK => $this->formatDayOfWeek($datum['label']),
                default => CarbonImmutable::createFromFormat($this->getLabelTimeFormat($this->interval), $datum['label'])->locale($this->locale())->translatedFormat($this->getLabelTimeFormat($this->interval))
            };

            if ($inPercent) {
                $result['data'][] = round((((int) ($datum['metric'])) / $result['total']) * 100, 2);

                return;
            }

            $result['data'][] = $this->formatNumber($datum['metric']);
        });

        return collect($result);
    }

    protected function getFormattedTrendsData(Collection $trendsData, bool $inPercent = false): Collection
    {
        if ($this->groupBy !== null) {
            return $trendsData->groupBy('grp')->map(function ($group) use ($inPercent) {
                return $this->formatTrends($group, $inPercent);
            });
        }

        return $this->formatTrends($trendsData, $inPercent);
    }

    /**
     * Include projections in the trends result.
     *
     * @param  Collection  $trends  The trends data
     * @return Collection The trends data with projections
     */
    protected function includeProjections(Collection $trends): Collection
    {
        // Calculate projections if we have projection parameters set
        if ($this->projectionTargetValue !== null || $this->projectionTargetDate !== null) {
            $this->calculateProjections($trends);
        }

        if (empty($this->projections)) {
            return $trends;
        }

        // If we have grouped data, we need to handle each group separately
        if ($trends->first() instanceof Collection) {
            return $trends->map(function (Collection $groupTrend, $key) {
                $result = $groupTrend->toArray();

                if (isset($this->projections['when'][$key])) {
                    $result['projections']['when'] = $this->projections['when'][$key];
                }

                if (isset($this->projections['date'][$key])) {
                    $result['projections']['date'] = $this->projections['date'][$key];
                }

                return collect($result);
            });
        }

        $result = $trends->toArray();

        if (isset($this->projections['when'])) {
            $result['projections']['when'] = $this->projections['when'];
        }

        if (isset($this->projections['date'])) {
            $result['projections']['date'] = $this->projections['date'];
        }

        return collect($result);
    }
}
