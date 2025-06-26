<?php

declare(strict_types=1);

namespace Beacon\Metrics;

use Beacon\Metrics\Concerns\WithAggregate;
use Beacon\Metrics\Concerns\WithAverage;
use Beacon\Metrics\Concerns\WithConditional;
use Beacon\Metrics\Concerns\WithCount;
use Beacon\Metrics\Concerns\WithDates;
use Beacon\Metrics\Concerns\WithFillMissingData;
use Beacon\Metrics\Concerns\WithGroup;
use Beacon\Metrics\Concerns\WithInterval;
use Beacon\Metrics\Concerns\WithMax;
use Beacon\Metrics\Concerns\WithMin;
use Beacon\Metrics\Concerns\WithProjection;
use Beacon\Metrics\Concerns\WithSum;
use Beacon\Metrics\Concerns\WithTable;
use Beacon\Metrics\Concerns\WithTrendMetric;
use Beacon\Metrics\Concerns\WithValue;
use Beacon\Metrics\Concerns\WithValueMetric;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\Config;

class Metrics
{
    use WithAggregate;
    use WithAverage;
    use WithConditional;
    use WithCount;
    use WithDates;
    use WithFillMissingData;
    use WithGroup;
    use WithInterval;
    use WithMax;
    use WithMin;
    use WithProjection;
    use WithSum;
    use WithTable;
    use WithTrendMetric;
    use WithValue;
    use WithValueMetric;

    protected array $projections = [];

    protected string $column = 'id';

    protected string $driver;

    protected function __construct(protected Builder|QueryBuilder|null $builder)
    {
        // @phpstan-ignore method.notFound
        $this->driver = $this->builder->getConnection()->getDriverName();

        $this->bootWithTable();
        $this->bootWithDates();
    }

    public static function query(Builder|QueryBuilder $builder): self
    {
        return new self($builder);
    }

    protected function locale(): string
    {
        return Config::get('app.locale');
    }
}
