<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

use Beacon\Metrics\Enums\Aggregate;
use Beacon\Metrics\Enums\Interval;
use Carbon\CarbonInterface;

trait WithAverage
{
    public function averageBetween(CarbonInterface $from, CarbonInterface $to, string $column): self
    {
        return $this
            ->average($column)
            ->between($from, $to);
    }

    public function averageByDay(string $column, int $count = 0): self
    {
        return $this->averageBy(Interval::DAY, $column, $count);
    }

    public function averageByDayOfTheWeek(string $column, int $count = 0): self
    {
        return $this->averageBy(Interval::DAY_OF_WEEK, $column, $count);
    }

    public function averageByMonth(string $column, int $count = 0): self
    {
        return $this->averageBy(Interval::MONTH, $column, $count);
    }

    public function averageByWeek(string $column, int $count = 0): self
    {
        return $this->averageBy(Interval::WEEK, $column, $count);
    }

    public function averageByYear(string $column, int $count = 0): self
    {
        return $this->averageBy(Interval::YEAR, $column, $count);
    }

    public function averageFrom(CarbonInterface $date, string $column): self
    {
        return $this
            ->average($column)
            ->from($date);
    }

    protected function averageBy(Interval $period, string $column = 'id', int $count = 0): self
    {
        return $this
            ->by($period, $count)
            ->aggregate(Aggregate::AVERAGE, $column);
    }
}
