<?php

declare(strict_types=1);

namespace Beacon\Metrics;

// @phpstan-ignore trait.unused
trait HasMetrics
{
    public static function metrics(): Metrics
    {

        return Metrics::query(static::query());
    }
}
