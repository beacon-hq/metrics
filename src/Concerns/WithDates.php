<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

use Beacon\Metrics\Enums\Interval;
use Beacon\Metrics\Enums\Period;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DatePeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use IntlCalendar;

trait WithDates
{
    /**
     * @var array{from: CarbonImmutable, to: CarbonImmutable}
     */
    protected array $between;

    protected string $dateColumn = 'created_at';

    public function between(CarbonInterface $from, CarbonInterface $to): self
    {
        $this->between = ['from' => CarbonImmutable::create($from), 'to' => CarbonImmutable::create($to)];

        return $this;
    }

    public function forDate(CarbonInterface $date): self
    {
        $this->between(CarbonImmutable::create($date)->startOfDay(), CarbonImmutable::create($date)->endOfDay());

        return $this;
    }

    public function forPeriod(Period|DatePeriod $period): self
    {
        $now = CarbonImmutable::now();

        if ($period instanceof DatePeriod) {
            $this->between(CarbonImmutable::create($period->getStartDate()), CarbonImmutable::create($period->getEndDate()));

            return $this;
        }

        match ($period) {
            Period::LAST_10_MINUTES => $this->between($now->subMinutes(10)->startOfMinute(), $now),
            Period::LAST_12_HOURS => $this->between($now->subHours(12), $now),
            Period::LAST_15_MINUTES => $this->between($now->subMinutes(15)->startOfMinute(), $now),
            Period::LAST_24_HOURS => $this->between($now->subHours(24), $now),
            Period::LAST_2_HOURS => $this->between($now->subHours(2), $now),
            Period::LAST_30_DAYS => $this->between($now->startOfDay()->subDays(30), $now->subDay()->endOfDay()),
            Period::LAST_30_DAYS_INCLUDING_TODAY => $this->between($now->startOfDay()->subDays(29), $now->endOfDay()),
            Period::LAST_30_MINUTES => $this->between($now->subMinutes(30)->startOfMinute(), $now),
            Period::LAST_365_DAYS => $this->between($now->startOfDay()->subDays(365), $now->subDay()->endOfDay()),
            Period::LAST_365_DAYS_INCLUDING_TODAY => $this->between($now->startOfDay()->subDays(364), $now->endOfDay()),
            Period::LAST_5_MINUTES => $this->between($now->subMinutes(5)->startOfMinute(), $now),
            Period::LAST_60_DAYS => $this->between($now->startOfDay()->subDays(60), $now->subDay()->endOfDay()),
            Period::LAST_60_DAYS_INCLUDING_TODAY => $this->between($now->startOfDay()->subDays(59), $now->endOfDay()),
            Period::LAST_6_HOURS => $this->between($now->subHours(6), $now),
            Period::LAST_7_DAYS => $this->between($now->startOfDay()->subDays(7), $now->subDay()->endOfDay()),
            Period::LAST_7_DAYS_INCLUDING_TODAY => $this->between($now->startOfDay()->subDays(6), $now->endOfDay()),
            Period::LAST_90_DAYS => $this->between($now->startOfDay()->subDays(90), $now->subDay()->endOfDay()),
            Period::LAST_90_DAYS_INCLUDING_TODAY => $this->between($now->startOfDay()->subDays(89), $now->endOfDay()),
            Period::LAST_HOUR => $this->between($now->subHour(), $now),
            Period::LAST_MINUTE => $this->between($now->subMinute(), $now),
            Period::LAST_THIRTY_SECONDS => $this->between($now->subSeconds(30), $now),
            Period::MONTH_TO_DATE => $this->between($now->startOfMonth(), $now->endOfDay()),
            Period::PREVIOUS_HOUR => $this->between($now->subHour()->startOfHour(), $now->subHour()->endOfHour()),
            Period::PREVIOUS_MINUTE => $this->between($now->subMinute()->startOfMinute(), $now->subMinute()->endOfMinute()),
            Period::PREVIOUS_MONTH => $this->between($now->subMonth()->startOfMonth(), $now->subMonth()->endOfMonth()),
            Period::PREVIOUS_YEAR => $this->between($now->subYear()->startOfYear(), $now->subYear()->endOfYear()),
            Period::TODAY => $this->between($now->startOfDay(), $now->endOfDay()),
            Period::YEAR_TO_DATE => $this->between($now->startOfYear(), $now->endOfDay()),
            Period::YESTERDAY => $this->between($now->subDay()->startOfDay(), $now->subDay()->endOfDay()),
        };

        return $this;
    }

    public function forMinute(int $minute): self
    {
        $minute = CarbonImmutable::now()->minute($minute);
        $this->between($minute->startOfMinute(), $minute->endOfMinute());

        return $this;
    }

    public function forHour(int $hour): self
    {
        $hour = CarbonImmutable::now()->hour($hour);
        $this->between($hour->startOfHour(), $hour->endOfHour());

        return $this;
    }

    public function forDay(int $day): self
    {
        $day = CarbonImmutable::now()->day($day);
        $this->between($day->startOfDay(), $day->endOfDay());

        return $this;
    }

    public function forWeek(int $week): self
    {
        $week = match ($this->driver) {
            'sqlite' => CarbonImmutable::now()->week($week),
            default => CarbonImmutable::now()->isoWeek($week),
        };

        $this->between($week->startOfWeek(), $week->endOfWeek());

        return $this;
    }

    public function forMonth(int $month): self
    {
        $month = CarbonImmutable::now()->month($month);
        $this->between($month->startOfMonth(), $month->endOfMonth());

        return $this;
    }

    public function forYear(int $year): self
    {
        $year = CarbonImmutable::now()->year($year);
        $this->between($year->startOfYear(), $year->endOfYear());

        return $this;
    }

    public function from(CarbonInterface $date): self
    {
        return $this->between(CarbonImmutable::create($date), CarbonImmutable::now());
    }

    public function dateColumn(string $column): self
    {
        $this->dateColumn = $column;

        return $this;
    }

    public function formatDatetime(): string
    {
        return sprintf('%s.%s', $this->table, $this->dateColumn);
    }

    public function bootWithDates(): void
    {
        $this->between = [
            'from' => CarbonImmutable::now()->subMonth()->startOfMonth(),
            'to' => CarbonImmutable::now()->subMonth()->endOfMonth(),
        ];
    }

    protected function getDateTimeFormat(Interval $interval): string
    {
        if ($this->driver === 'mysql' || $this->driver === 'mariadb') {
            return match ($interval) {
                Interval::SECOND => '%Y-%m-%d %H:%i:%s',
                Interval::MINUTE => '%Y-%m-%d %H:%i',
                Interval::HOUR => '%Y-%m-%d %H',
                Interval::DAY => '%Y-%m-%d',
                Interval::DAY_OF_WEEK => '%W',
                Interval::WEEK => match (now()->startOfWeek()->format('l')) {
                    'Sunday' => '%X-W%V',
                    default => '%x-W%v',
                },
                Interval::MONTH => '%Y-%m',
                Interval::YEAR => '%Y',
            };
        }

        if ($this->driver === 'pgsql') {
            return match ($interval) {
                Interval::SECOND => 'YYYY-MM-DD HH24:MI:SS',
                Interval::MINUTE => 'YYYY-MM-DD HH24:MI',
                Interval::HOUR => 'YYYY-MM-DD HH24',
                Interval::DAY => 'YYYY-MM-DD',
                Interval::DAY_OF_WEEK => 'Day',
                Interval::WEEK => match (IntlCalendar::createInstance(null, $this->locale())->getFirstDayOfWeek()) {
                    IntlCalendar::DOW_SUNDAY => 'IYYY-IW',
                    default => 'IYYY-IW',
                },
                Interval::MONTH => 'YYYY-MM',
                Interval::YEAR => 'YYYY',
            };
        }

        return match ($interval) {
            Interval::SECOND => '%Y-%m-%d %H:%M:%S',
            Interval::MINUTE => '%Y-%m-%d %H:%M',
            Interval::HOUR => '%Y-%m-%d %H',
            Interval::DAY => '%Y-%m-%d',
            Interval::DAY_OF_WEEK => '%w',
            Interval::WEEK => '%Y-W%W', // Overridden for SQLite @see WithInterval::parseDate()
            Interval::MONTH => '%Y-%m',
            Interval::YEAR => '%Y',
        };
    }

    protected function getLabelTimeFormat(Interval $interval): string
    {
        return match ($interval) {
            Interval::SECOND => 'Y-m-d H:i:s',
            Interval::MINUTE => 'Y-m-d H:i',
            Interval::HOUR => 'Y-m-d H',
            Interval::DAY => 'Y-m-d',
            Interval::DAY_OF_WEEK => 'l',
            Interval::WEEK => 'Y-\WW',
            Interval::MONTH => 'Y-m',
            Interval::YEAR => 'Y',
        };
    }

    protected function applyBetween(Builder|QueryBuilder $query, bool $cast = true): Builder|QueryBuilder
    {
        return $query->whereBetween(
            DB::raw($this->table.'.'.$this->dateColumn),
            [
                $this->formatDatetimeFloor($this->between['from'], $cast),
                $this->formatDatetimeCeil($this->between['to']),
            ]
        );
    }
}
