<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

trait WithValueMetric
{
    public string $query;

    public function valueWithPrevious(): Collection
    {
        $interval = $this->between['from']->diff($this->between['to']);
        $previous = (clone $this)->between($this->between['from']->sub($interval), $this->between['to']->sub((clone $interval)->addSeconds(1)))->value();

        $result['value'] = $this->value();
        $result['previous'] = [
            'value' => $previous,
        ];

        $difference = $result['value'] - $previous;
        if ($difference > 0) {
            $result['previous'] = [
                'type' => 'increase',
                'value' => $previous,
                'difference' => $difference,
                'percentage' => $this->calculatePercentage($previous, $result['value']),
            ];
        } elseif ($difference < 0) {
            $result['previous'] = [
                'type' => 'decrease',
                'value' => $previous,
                'difference' => abs($difference),
                'percentage' => $this->calculatePercentage($result['value'], $previous),
            ];
        }

        return collect($result);
    }

    public function value(): int|float|Collection
    {
        $query = $this->applyBetween((clone $this->builder), false);

        $columns = [$this->selectData()];
        if ($this->groupBy !== null) {
            $columns[] = $this->groupBy;
        }

        $query->select($columns)->when($this->groupBy !== null, function (Builder|QueryBuilder $query) {
            $query->groupBy('grp')->orderBy('grp');
        });

        $this->query = $query->toRawSql();

        if ($this->groupBy !== null) {
            return $query->get()->mapWithKeys(
                function (object $datum): array {
                    return [
                        // @phpstan-ignore property.notFound,property.notFound
                        $datum->grp => $this->formatNumber($datum->metric),
                    ];
                });
        }

        return $this->formatNumber($query->first('metric')->metric);
    }

    protected function calculatePercentage(float|int $current, float|int $previous): int|float
    {
        $percentage = abs((($current - $previous) / $previous) * 100);

        return $this->formatNumber($percentage, 2);
    }
}
