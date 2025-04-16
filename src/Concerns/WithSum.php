<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

use Beacon\Metrics\Enums\Aggregate;
use Beacon\Metrics\Enums\Interval;
use Carbon\CarbonInterface;

trait WithSum
{
    public function sumBetween(CarbonInterface $from, CarbonInterface $to, string $column): self
    {
        return $this
            ->sum($column)
            ->between($from, $to);
    }

    public function sumByDay(string $column, int $count = 0): self
    {
        return $this->sumBy(Interval::DAY, $column, $count);
    }

    public function sumByDayOfTheWeek(string $column, int $count = 0): self
    {
        return $this->sumBy(Interval::DAY_OF_WEEK, $column, $count);
    }

    public function sumByMonth(string $column, int $count = 0): self
    {
        return $this->sumBy(Interval::MONTH, $column, $count);
    }

    public function sumByWeek(string $column, int $count = 0): self
    {
        return $this->sumBy(Interval::WEEK, $column, $count);
    }

    public function sumByYear(string $column, int $count = 0): self
    {
        return $this->sumBy(Interval::YEAR, $column, $count);
    }

    public function sumFrom(CarbonInterface $date, string $column): self
    {
        return $this
            ->sum($column)
            ->from($date);
    }

    protected function sumBy(Interval $period, string $column = 'id', int $count = 0): self
    {
        return $this
            ->by($period, $count)
            ->aggregate(Aggregate::SUM, $column);
    }
}
