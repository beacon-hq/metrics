<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

use Carbon\CarbonInterface;
use Beacon\Metrics\Enums\Aggregate;
use Beacon\Metrics\Enums\Interval;

trait WithMax
{
    public function maxBetween(CarbonInterface $from, CarbonInterface $to, string $column): self
    {
        return $this
            ->max($column)
            ->between($from, $to);
    }

    public function maxByDay(string $column, int $count = 0): self
    {
        return $this->maxBy(Interval::DAY, $column, $count);
    }

    public function maxByDayOfTheWeek(string $column, int $count = 0): self
    {
        return $this->maxBy(Interval::DAY_OF_WEEK, $column, $count);
    }

    public function maxByMonth(string $column, int $count = 0): self
    {
        return $this->maxBy(Interval::MONTH, $column, $count);
    }

    public function maxByWeek(string $column, int $count = 0): self
    {
        return $this->maxBy(Interval::WEEK, $column, $count);
    }

    public function maxByYear(string $column, int $count = 0): self
    {
        return $this->maxBy(Interval::YEAR, $column, $count);
    }

    public function maxFrom(CarbonInterface $date, string $column): self
    {
        return $this
            ->max($column)
            ->from($date);
    }

    protected function maxBy(Interval $period, string $column = 'id', int $count = 0): self
    {
        return $this
            ->by($period, $count)
            ->aggregate(Aggregate::MAX, $column);
    }
}
