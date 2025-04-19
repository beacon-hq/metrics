<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

use Beacon\Metrics\Exceptions\InvalidDateRangeException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

trait WithValueMetric
{
    public string $query;

    protected bool $withPrevious = false;

    public function withPrevious($enabled = true): self
    {
        $this->withPrevious = $enabled;

        return $this;
    }

    public function value(): int|float|Collection
    {
        if ($this->withPrevious) {
            return $this->valueWithPrevious();
        }

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

    protected function valueWithPrevious(): Collection
    {
        if ($this->between['from'] === null) {
            throw new InvalidDateRangeException(
                'You cannot use the withPrevious() method when using the all() method.'
            );
        }

        $interval = $this->between['from']->diff($this->between['to']);
        $previous = (clone $this)->withPrevious(false)->between($this->between['from']->sub($interval), $this->between['to']->sub((clone $interval)->addSeconds(1)))->value();

        $result['value'] = $this->withPrevious(false)->value();
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

    protected function calculatePercentage(float|int $current, float|int $previous): int|float
    {
        $percentage = abs((($current - $previous) / $previous) * 100);

        return $this->formatNumber($percentage, 2);
    }
}
