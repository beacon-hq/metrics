<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

use Beacon\Metrics\Enums\Aggregate;
use Beacon\Metrics\Enums\Interval;
use Carbon\CarbonInterface;

trait WithCount
{
    public function countBetween(CarbonInterface $from, CarbonInterface $to, string $column = 'id'): self
    {
        return $this
            ->count($column)
            ->between($from, $to);
    }

    public function countBySecond(string $column = 'id', int $count = 0): self
    {
        return $this->countBy(Interval::SECOND, $column, $count);
    }

    public function countByMinute(string $column = 'id', int $count = 0): self
    {
        return $this->countBy(Interval::MINUTE, $column, $count);
    }

    public function countByHour(string $column = 'id', int $count = 0): self
    {
        return $this->countBy(Interval::HOUR, $column, $count);
    }

    public function countByDay(string $column = 'id', int $count = 0): self
    {
        return $this->countBy(Interval::DAY, $column, $count);
    }

    public function countByDayOfTheWeek(string $column = 'id', int $count = 0): self
    {
        return $this->countBy(Interval::DAY_OF_WEEK, $column, $count);
    }

    public function countByMonth(string $column = 'id', int $count = 0): self
    {
        return $this->countBy(Interval::MONTH, $column, $count);
    }

    public function countByWeek(string $column = 'id', int $count = 0): self
    {
        return $this->countBy(Interval::WEEK, $column, $count);
    }

    public function countByYear(string $column = 'id', int $count = 0): self
    {
        return $this->countBy(Interval::YEAR, $column, $count);
    }

    public function countFrom(CarbonInterface $date, string $column = 'id'): self
    {
        return $this
            ->count($column)
            ->from($date);
    }

    protected function countBy(Interval $period, string $column = 'id', int $count = 0): self
    {
        return $this
            ->by($period, $count)
            ->aggregate(Aggregate::COUNT, $column);
    }
}
