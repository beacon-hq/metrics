<?php

declare(strict_types=1);

namespace Beacon\Metrics\Values\Collections;

use Bag\Collection;
use Beacon\Metrics\Values\TrendMetric;

class TrendMetricCollection extends Collection
{
    public function assoc(bool $withTotal = false): Collection
    {
        return $this->map(fn (TrendMetric $metric) => $metric->assoc($withTotal));
    }
}
