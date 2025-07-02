<?php

declare(strict_types=1);

use Beacon\Metrics\Concerns\WithConditional;
use Beacon\Metrics\Metrics;
use Illuminate\Support\Carbon;

covers(WithConditional::class);

it('applies conditional when true', function ($db) {
    createTestData($db);

    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics
        ->when(true, fn (Metrics $metrics) => $metrics->minBetween(Carbon::parse('2025-04-06'), now(), 'value'))
        ->byDay()
        ->trends();

    expect($trends->labels)->toHaveCount(5)
        ->and($trends->labels[0])
        ->toBe('2025-04-06')
        ->and($trends->labels[4])
        ->toBe('2025-04-10');
})->with('databases');

it('does not apply conditional when false', function ($db) {
    createTestData($db);

    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics
        ->when(false, fn (Metrics $metrics) => $metrics->minBetween(Carbon::parse('2025-04-06'), now(), 'value'))
        ->byDay()
        ->trends();

    expect($trends->labels)->toHaveCount(3)
        ->and($trends->labels[0])
        ->toBe('2025-03-10')
        ->and($trends->labels[2])
        ->toBe('2025-03-27');
})->with('databases');
