<?php

declare(strict_types=1);

use Beacon\Metrics\Values\TrendMetric;
use Illuminate\Support\Collection;

it('returns an assoc array', function () {
    $trends = TrendMetric::from(labels: ['Jan', 'Feb', 'Mar'], data: [10, 20, 30], total: 60);

    $assoc = $trends->assoc();

    expect($assoc)
        ->toBeInstanceOf(Collection::class)
        ->and($assoc->toArray())
        ->toBe(['Jan' => 10, 'Feb' => 20, 'Mar' => 30]);
});
