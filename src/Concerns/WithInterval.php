<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

use Beacon\Metrics\Enums\Interval;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

trait WithInterval
{
    protected int $count = 1;

    protected Interval $interval = Interval::DAY;

    public function bySecond(int $count = 1): self
    {
        return $this->by(Interval::SECOND, $count);
    }

    public function byMinute(int $count = 1): self
    {
        return $this->by(Interval::MINUTE, $count);
    }

    public function byHour(int $count = 1): self
    {
        return $this->by(Interval::HOUR, $count);
    }

    public function byDayOfWeek(int $count = 1): self
    {
        return $this->by(Interval::DAY_OF_WEEK, $count);
    }

    public function byDay(int $count = 1): self
    {
        return $this->by(Interval::DAY, $count);
    }

    public function byWeek(int $count = 1): self
    {
        return $this->by(Interval::WEEK, $count);
    }

    public function byMonth(int $count = 1): self
    {
        return $this->by(Interval::MONTH, $count);
    }

    public function byYear(int $count = 1): self
    {
        return $this->by(Interval::YEAR, $count);
    }

    public function formatDatetimeFloor(?CarbonImmutable $date, bool $cast = true): string
    {
        if ($date === null) {
            $minDate = (clone $this->builder)
                ->selectRaw(
                    $this->parseDate(
                        $this->interval,
                        sprintf('MIN(%s.%s)', $this->table, $this->dateColumn)
                    ).' as min_date'
                )
                ->limit(1)
                ->pluck('min_date')
                ->first();

            $date = CarbonImmutable::parse($minDate);

            if ($this->between['from'] === null) {
                $this->between['from'] = $date;
            }
        }

        $date = match ($this->interval) {
            Interval::SECOND => $date->format('Y-m-d H:i:s'),
            Interval::MINUTE => $date->format('Y-m-d H:i:00'),
            Interval::HOUR => $date->format('Y-m-d H:00:00'),
            Interval::DAY, Interval::DAY_OF_WEEK => $date->format('Y-m-d 00:00:00'),
            Interval::WEEK => $date->startOfWeek()->format('Y-m-d 00:00:00'),
            Interval::MONTH => $date->format('Y-m-01 00:00:00'),
            Interval::YEAR => $date->format('Y-01-01 00:00:00'),
        };

        return '\''.$date.'\''.(($cast && $this->driver === 'pgsql') ? '::timestamp' : '');
    }

    public function formatDatetimeCeil(CarbonImmutable $date): string
    {
        $date = match ($this->interval) {
            Interval::SECOND => $date->format('Y-m-d H:i:s'),
            Interval::MINUTE => $date->format('Y-m-d H:i:59'),
            Interval::HOUR => $date->format('Y-m-d H:59:59'),
            Interval::DAY, Interval::DAY_OF_WEEK => $date->format('Y-m-d 23:59:59'),
            Interval::WEEK => $date->endOfWeek()->format('Y-m-d 23:59:59'),
            Interval::MONTH => $date->format('Y-m-'.$date->endOfMonth()->format('d').' 23:59:59'),
            Interval::YEAR => $date->format('Y-12-'.$date->endOfMonth()->format('d').' 23:59:59'),
        };

        return '\''.$date.'\'';
    }

    protected function by(Interval $interval, int $count = 1): self
    {
        $this->interval = $interval;
        $this->count = $count;

        return $this;
    }

    protected function getIntervalLabel(Interval $interval, string $column): Expression
    {
        if ($interval === Interval::WEEK) {
            $interval = Interval::DAY;
        }

        return DB::raw($this->parseDate($interval, $column).' AS label');
    }

    protected function getIntervalGroup(Interval $interval, string $column): Expression
    {
        return DB::raw($this->parseDate($interval, $column).' AS grp_label');
    }

    protected function parseDate(Interval $interval, ?string $column = null): string
    {
        return match ($this->driver) {
            'mysql', 'mariadb' => sprintf('DATE_FORMAT(%s, \'%s\')', $column ?? ($this->table.'.'.$this->dateColumn), $this->getDateTimeFormat($interval)),
            'pgsql' => sprintf('TO_CHAR(%s, \'%s\')', $column ?? ($this->table.'.'.$this->dateColumn), $this->getDateTimeFormat($interval)),
            default => match ($interval) { // sqlite may or may not support 1-based weeks
                Interval::WEEK => 'COALESCE(
                                    STRFTIME(\'%Y-W%V\', '.($column ?? ($this->table.'.'.$this->dateColumn)).'),
                                    CASE
                                        0+STRFTIME(\'%W\', '.($column ?? ($this->table.'.'.$this->dateColumn)).')
                                        WHEN 53 THEN CONCAT(1+STRFTIME(\'%Y\', '.($column ?? ($this->table.'.'.$this->dateColumn)).'), \'-W01\')
                                        WHEN 0 THEN STRFTIME(\'%Y-W01\', '.($column ?? ($this->table.'.'.$this->dateColumn)).')
                                        WHEN 1 THEN STRFTIME(\'%Y-W02\', '.($column ?? ($this->table.'.'.$this->dateColumn)).')
                                        WHEN 2 THEN STRFTIME(\'%Y-W03\', '.($column ?? ($this->table.'.'.$this->dateColumn)).')
                                        WHEN 3 THEN STRFTIME(\'%Y-W04\', '.($column ?? ($this->table.'.'.$this->dateColumn)).')
                                        WHEN 4 THEN STRFTIME(\'%Y-W05\', '.($column ?? ($this->table.'.'.$this->dateColumn)).')
                                        WHEN 5 THEN STRFTIME(\'%Y-W06\', '.($column ?? ($this->table.'.'.$this->dateColumn)).')
                                        WHEN 6 THEN STRFTIME(\'%Y-W07\', '.($column ?? ($this->table.'.'.$this->dateColumn)).')
                                        WHEN 7 THEN STRFTIME(\'%Y-W08\', '.($column ?? ($this->table.'.'.$this->dateColumn)).')
                                        WHEN 8 THEN STRFTIME(\'%Y-W09\', '.($column ?? ($this->table.'.'.$this->dateColumn)).')
                                        ELSE CONCAT(STRFTIME(\'%Y-W\', '.($column ?? ($this->table.'.'.$this->dateColumn)).'), 1+STRFTIME(\'%W\', '.($column ?? ($this->table.'.'.$this->dateColumn)).'))
                                    END
                                )',
                default => sprintf('STRFTIME(\'%s\', %s)', $this->getDateTimeFormat($interval), $column ?? ($this->table.'.'.$this->dateColumn))
            },
        };
    }

    protected function getIntervalSequence(?string $column = null): string
    {
        return match ($this->driver) {
            'mysql' => sprintf('DATE_ADD(%s, INTERVAL %s)', $column ?? $this->table.'.'.$this->dateColumn, $this->getIntervalPeriod($this->count)),
            'pgsql' => sprintf('%s + INTERVAL \'%s\'', $column ?? $this->table.'.'.$this->dateColumn, $this->getIntervalPeriod($this->count)),
            default => sprintf('DATETIME(%s, \'+%s\')', $column ?? $this->table.'.'.$this->dateColumn, $this->getIntervalPeriod($this->count)),
        };
    }

    protected function getIntervalPeriod(int $count): string
    {
        if ($this->driver === 'mysql' || $this->driver === 'mariad') {
            return match ($this->interval) {
                Interval::SECOND => sprintf('%s %s', $count, 'SECOND'),
                Interval::MINUTE => sprintf('%s %s', $count, 'MINUTE'),
                Interval::HOUR => sprintf('%s %s', $count, 'HOUR'),
                Interval::DAY, Interval::DAY_OF_WEEK => sprintf('%s %s', $count, 'DAY'),
                Interval::WEEK => sprintf('%s %s', $count, 'WEEK'),
                Interval::MONTH => sprintf('%s %s', $count, 'MONTH'),
                Interval::YEAR => sprintf('%s %s', $count, 'YEAR'),
            };
        }

        if ($this->driver === 'pgsql') {
            return match ($this->interval) {
                Interval::SECOND => sprintf('%s %s', $count, 'seconds'),
                Interval::MINUTE => sprintf('%s %s', $count, 'minutes'),
                Interval::HOUR => sprintf('%s %s', $count, 'hours'),
                Interval::DAY, Interval::DAY_OF_WEEK => sprintf('%s %s', $count, 'days'),
                Interval::WEEK => sprintf('%s %s', $count, 'weeks'),
                Interval::MONTH => sprintf('%s %s', $count, 'months'),
                Interval::YEAR => sprintf('%s %s', $count, 'years'),
            };
        }

        return match ($this->interval) {
            Interval::SECOND => sprintf('%s %s', $count, 'seconds'),
            Interval::MINUTE => sprintf('%s %s', $count, 'minutes'),
            Interval::HOUR => sprintf('%s %s', $count, 'hours'),
            Interval::DAY, Interval::DAY_OF_WEEK => sprintf('%s %s', $count, 'days'),
            Interval::WEEK => sprintf('%s %s', $count * 7, 'days'), // SQLite doesn't support weeks
            Interval::MONTH => sprintf('%s %s', $count, 'months'),
            Interval::YEAR => sprintf('%s %s', $count, 'years'),
        };
    }
}
