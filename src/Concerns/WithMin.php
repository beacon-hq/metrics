<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

use Beacon\Metrics\Enums\Aggregate;
use Beacon\Metrics\Enums\Interval;
use Carbon\CarbonInterface;

trait WithMin
{
    public function minBetween(CarbonInterface $from, CarbonInterface $to, string $column): self
    {
        return $this
            ->min($column)
            ->between($from, $to);
    }

    public function minBySecond(string $column, int $count = 1): self
    {
        return $this->minBy(Interval::SECOND, $column, $count);
    }

    public function minByMinute(string $column, int $count = 1): self
    {
        return $this->minBy(Interval::MINUTE, $column, $count);
    }

    public function minByHour(string $column, int $count = 1): self
    {
        return $this->minBy(Interval::HOUR, $column, $count);
    }

    public function minByDay(string $column, int $count = 1): self
    {
        return $this->minBy(Interval::DAY, $column, $count);
    }

    public function minByDayOfWeek(string $column, int $count = 1): self
    {
        return $this->minBy(Interval::DAY_OF_WEEK, $column, $count);
    }

    public function minByMonth(string $column, int $count = 1): self
    {
        return $this->minBy(Interval::MONTH, $column, $count);
    }

    public function minByWeek(string $column, int $count = 1): self
    {
        return $this->minBy(Interval::WEEK, $column, $count);
    }

    public function minByYear(string $column, int $count = 1): self
    {
        return $this->minBy(Interval::YEAR, $column, $count);
    }

    public function minFrom(CarbonInterface $date, string $column): self
    {
        return $this
            ->min($column)
            ->from($date);
    }

    protected function minBy(Interval $period, string $column = 'id', int $count = 1): self
    {
        return $this
            ->by($period, $count)
            ->aggregate(Aggregate::MIN, $column);
    }
}
