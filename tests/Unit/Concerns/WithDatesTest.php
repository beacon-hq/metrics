<?php

declare(strict_types=1);

use Beacon\Metrics\Concerns\WithDates;
use Beacon\Metrics\Enums\Period;
use Beacon\Metrics\Metrics;
use Illuminate\Support\Carbon;

covers(WithDates::class);

it('counts between', function () {
    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->count()->between(Carbon::parse('2025-04-06'), now())->byDay()->trends();

    expect($trends['labels'])->toHaveCount(5)
        ->and($trends['labels'][0])
        ->toBe('2025-04-06')
        ->and($trends['labels'][4])
        ->toBe('2025-04-10');
});

it('counts from', function () {
    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->count()->from(Carbon::parse('2025-04-06'))->byDay()->trends();

    expect($trends['labels'])->toHaveCount(5)
        ->and($trends['labels'][0])
        ->toBe('2025-04-06')
        ->and($trends['labels'][4])
        ->toBe('2025-04-10');
});

it('counts period', function () {
    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->count()->period(Period::LAST_30_DAYS)->trends();

    expect($trends['labels'])->toHaveCount(8)
        ->and($trends['labels'][0])
        ->toBe('2025-03-20')
        ->and($trends['labels'][7])
        ->toBe('2025-04-10');
});

it('sets date column', function () {
    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $metrics->dateColumn('updated_at');

    expect($this->prop($metrics, 'dateColumn'))->toBe('updated_at');
});

it('formats day of the week', function () {
    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->count()->from(now()->subYears(2))->byDayOfWeek()->trends();

    expect($trends['labels'])->toHaveCount(7)
        ->and($trends['labels'][0])
        ->toBe('Sunday')
        ->and($trends['labels'][6])
        ->toBe('Saturday');
});
