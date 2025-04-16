<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

use Beacon\Metrics\Enums\Interval;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait WithTrendMetric
{
    public function trends(bool $inPercent = false): Collection
    {
        $baseQuery = DB::query()
            ->select(DB::raw($this->formatDatetimeFloor($this->between['from']).' AS dt'));

        $recursiveQuery = (clone $this->builder)->newQuery()->select(DB::raw($this->getIntervalSequence('date_series.dt').' AS dt'))
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

        /** @var \Staudenmeir\LaravelCte\Query\Builder $query */
        // @phpstan-ignore method.notFound
        $query = (clone $this->builder)
            ->withRecursiveExpression('date_series', $builder, ['dt']);

        $columns = [
            $this->selectData(),
            $this->getIntervalLabel($this->interval, 'date_series.dt'),
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
            ->groupBy('label')
            ->orderBy('label');

        $this->query = $query->toRawSql();

        $results = $query->get();
        $trendsData = $results->map(fn (object $datum) => (array) $datum);

        return $this->getFormattedTrendsData($trendsData, $inPercent);
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

        $result['total'] = $total->value();
        if ($this->groupBy !== null) {
            $result['total'] = $result['total']->get($data->first()['grp']);
        }

        $data->each(function ($datum) use ($inPercent, &$result) {
            if ($this->interval === Interval::WEEK) {
                $result['labels'][] = CarbonImmutable::create(Str::substr($datum['label'], 0, 4))->isoWeek((int) Str::substr($datum['label'], 6))->translatedFormat($this->getLabelTimeFormat($this->interval));
            } else {
                $result['labels'][] = CarbonImmutable::createFromFormat($this->getLabelTimeFormat($this->interval), $datum['label'])->locale($this->locale())->translatedFormat($this->getLabelTimeFormat($this->interval));
            }

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
        $trendsData = $trendsData->when($this->interval === Interval::DAY_OF_WEEK)->map(function ($datum) {
            $datum['label'] = CarbonImmutable::parse($datum['label'])->translatedFormat('l');
        });

        if ($this->groupBy !== null) {
            return $trendsData->groupBy('grp')->map(function ($group) use ($inPercent) {
                return $this->formatTrends($group, $inPercent);
            });
        }

        return $this->formatTrends($trendsData, $inPercent);
    }
}
