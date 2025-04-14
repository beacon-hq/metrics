<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

use Beacon\Metrics\Enums\Aggregate;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

trait WithAggregate
{
    protected Aggregate $aggregate = Aggregate::COUNT;

    public function average(string $column): self
    {
        return $this->aggregate(Aggregate::AVERAGE, $column);
    }

    public function count(string $column = 'id'): self
    {
        return $this->aggregate(Aggregate::COUNT, $column);
    }

    public function max(string $column): self
    {
        return $this->aggregate(Aggregate::MAX, $column);
    }

    public function min(string $column): self
    {
        return $this->aggregate(Aggregate::MIN, $column);
    }

    public function sum(string $column): self
    {
        return $this->aggregate(Aggregate::SUM, $column);
    }

    protected function selectData(?string $column = null): Expression
    {
        $aggregateExpression = "{$this->aggregate->value}(".($column ?? $this->column).')';
        if ($this->fillMissing) {
            return DB::raw("COALESCE(NULLIF($aggregateExpression, 0), {$this->missingDataValue}) as metric");
        } else {
            return DB::raw("$aggregateExpression as metric");
        }
    }

    protected function aggregate(Aggregate $aggregate, string $column): self
    {
        $this->aggregate = $aggregate;
        $this->column = $this->table.'.'.$column;

        return $this;
    }
}
