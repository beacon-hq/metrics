<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

use Beacon\Metrics\Enums\PreviousType;
use Beacon\Metrics\Exceptions\InvalidDateRangeException;
use Beacon\Metrics\Values\Collections\ValueMetricCollection;
use Beacon\Metrics\Values\ValueMetric;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

trait WithValueMetric
{
    public string $query;

    protected bool $withPrevious = false;

    public function withPrevious($enabled = true): self
    {
        $this->withPrevious = $enabled;

        return $this;
    }

    public function value(): ValueMetric|ValueMetricCollection
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
            $result = $query->get();

            return ValueMetricCollection::wrap($result->mapWithKeys(
                function (object $datum): array {
                    return [
                        // @phpstan-ignore property.notFound,property.notFound
                        $datum->grp => ValueMetric::from(value: $this->formatNumber($datum->metric)),
                    ];
                }));
        }

        return ValueMetric::from(value: $this->formatNumber($query->first('metric')->metric));
    }

    protected function valueWithPrevious(): ValueMetric
    {
        if ($this->between['from'] === null) {
            throw new InvalidDateRangeException(
                'You cannot use the withPrevious() method when using the all() method.'
            );
        }

        $interval = $this->between['from']->diff($this->between['to']);
        $previous = (clone $this)->withPrevious(false)->between($this->between['from']->sub($interval), $this->between['to']->sub((clone $interval)->addSeconds(1)))->value()->value;

        $result['value'] = $this->withPrevious(false)->value()->value;

        $difference = $result['value'] - $previous;
        if ($difference > 0) {
            $result['previous'] = [
                'type' => PreviousType::INCREASE,
                'value' => $previous,
                'difference' => $difference,
                'percentage' => $this->calculatePercentage($previous, $result['value']),
            ];
        } elseif ($difference < 0) {
            $result['previous'] = [
                'type' => PreviousType::DECREASE,
                'value' => $previous,
                'difference' => abs($difference),
                'percentage' => $this->calculatePercentage($result['value'], $previous),
            ];
        } else {
            $result['previous'] = [
                'type' => PreviousType::IDENTICAL,
                'value' => $previous,
                'difference' => 0,
                'percentage' => 0,
            ];
        }

        return ValueMetric::from($result);
    }

    protected function calculatePercentage(float|int $current, float|int $previous): int|float
    {
        $percentage = abs((($current - $previous) / $previous) * 100);

        return $this->formatNumber($percentage, 2);
    }
}
