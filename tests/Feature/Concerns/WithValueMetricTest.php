<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Beacon\Metrics\Concerns\WithAggregate;
use Beacon\Metrics\Concerns\WithValueMetric;
use Beacon\Metrics\Metrics;
use Illuminate\Support\Facades\DB;

covers(
    WithValueMetric::class,
    WithAggregate::class,
);

beforeEach(function () {
    CarbonImmutable::setTestNow(CarbonImmutable::create(2025, 04, 10, 2, 38, 15));
});

it('calculates value metric no dates', function ($aggregate, $metric) {
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);
    $value = $metrics->$aggregate('value')->value();

    expect($value)->toBe($metric, $metrics->query);
})->with('aggregate values');

it('calculates value metric for day', function ($for, $aggregate, $metric) {
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);
    $value = $metrics->$aggregate('value')->forDay($for)->value();

    expect($value)->toBe($metric, $metrics->query);
})->with('aggregate values for day');

it('calculates value metric for week', function ($for, $aggregate, $metric) {
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);
    $value = $metrics->$aggregate('value')->forWeek($for)->value();

    expect($value)->toBe($metric, $metrics->query);
})->with('aggregate values for week');

it('calculates value metric for month', function ($for, $aggregate, $metric) {
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);
    $value = $metrics->$aggregate('value')->forMonth($for)->value();

    expect($value)->toBe($metric, $metrics->query);
})->with('aggregate values for month');

it('calculates value metric for year', function ($for, $aggregate, $metric) {
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);
    $value = $metrics->$aggregate('value')->forYear($for)->value();

    expect($value)->toBe($metric, $metrics->query);
})->with('aggregate values for year');

it('calculates value metric and previous for day', function ($for, $aggregate, $metric) {
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);
    $value = $metrics->$aggregate('value')->forDay($for)->valueWithPrevious();

    expect($value->toArray())->toBe($metric, $metrics->query);
})->with('previous for day');

it('calculates value metric and previous for week', function ($for, $aggregate, $metric) {
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);
    $value = $metrics->$aggregate('value')->forWeek($for)->valueWithPrevious();

    expect($value->toArray())->toBe($metric, $metrics->query);
})->with('previous for week');

it('calculates value metric and previous for month', function ($for, $aggregate, $metric) {
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);
    $value = $metrics->$aggregate('value')->forMonth($for)->valueWithPrevious();

    expect($value->toArray())->toBe($metric, $metrics->query);
})->with('previous for month');

it('calculates value metric and previous for year', function ($for, $aggregate, $metric) {
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);
    $value = $metrics->$aggregate('value')->forYear($for)->valueWithPrevious();

    expect($value->toArray())->toBe($metric, $metrics->query);
})->with('previous for year');

it('calculates value metric and previous with decrease', function () {
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    DB::table('test_data')->insert([
        'name' => 'Item 15',
        'value' => 115,
        'category' => 'category1',
        'created_at' => CarbonImmutable::now()->day(5)->subDay(),
        'updated_at' => CarbonImmutable::now()->day(5)->subDay(),
    ]);

    $value = $metrics->sum('value')->forDay(5)->valueWithPrevious();

    expect($value->toArray())->toBe([
        'value' => 0,
        'previous' => [
            'type' => 'decrease',
            'value' => 115,
            'difference' => 115,
            'percentage' => 100,
        ],
    ], $metrics->query);
});

it('casts floats correctly', function () {
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $builder->insert([
        [
            'name' => 'Item 3',
            'value' => 80.987654,
            'category' => 'category3',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $value = $metrics->sum('value')->from(now()->subDays(7))->byDay()->value();

    expect($value)->toBeFloat();
});

dataset('aggregate values', [
    [
        'aggregate' => 'count',
        'metric' => 3,
    ],
    [
        'aggregate' => 'sum',
        'metric' => 210,
    ],
    [
        'aggregate' => 'average',
        'metric' => 70,
    ],
    [
        'aggregate' => 'min',
        'metric' => 60,
    ],
    [
        'aggregate' => 'max',
        'metric' => 80,
    ],
]);

dataset('aggregate values for day', [
    [
        'for' => 10,
        'aggregate' => 'count',
        'metric' => 11,
    ],
    [
        'for' => 9,
        'aggregate' => 'count',
        'metric' => 3,
    ],
    [
        'for' => 10,
        'aggregate' => 'sum',
        'metric' => 2310,
    ],
    [
        'for' => 9,
        'aggregate' => 'sum',
        'metric' => 420,
    ],
    [
        'for' => 10,
        'aggregate' => 'average',
        'metric' => 210,
    ],
    [
        'for' => 9,
        'aggregate' => 'average',
        'metric' => 140,
    ],
    [
        'for' => 10,
        'aggregate' => 'min',
        'metric' => 160,
    ],
    [
        'for' => 9,
        'aggregate' => 'min',
        'metric' => 130,
    ],
    [
        'for' => 10,
        'aggregate' => 'max',
        'metric' => 260,
    ],
    [
        'for' => 9,
        'aggregate' => 'max',
        'metric' => 150,
    ],
]);

dataset('aggregate values for week', [
    [
        'for' => 15,
        'aggregate' => 'count',
        'metric' => 16,
    ],
    [
        'for' => 14,
        'aggregate' => 'count',
        'metric' => 2,
    ],
    [
        'for' => 15,
        'aggregate' => 'sum',
        'metric' => 2960,
    ],
    [
        'for' => 14,
        'aggregate' => 'sum',
        'metric' => 190,
    ],
    [
        'for' => 15,
        'aggregate' => 'average',
        'metric' => 185,
    ],
    [
        'for' => 14,
        'aggregate' => 'average',
        'metric' => 95,
    ],
    [
        'for' => 15,
        'aggregate' => 'min',
        'metric' => 110,
    ],
    [
        'for' => 14,
        'aggregate' => 'min',
        'metric' => 90,
    ],
    [
        'for' => 15,
        'aggregate' => 'max',
        'metric' => 260,
    ],
    [
        'for' => 14,
        'aggregate' => 'max',
        'metric' => 100,
    ],
]);

dataset('aggregate values for month', [
    [
        'for' => CarbonImmutable::now()->month,
        'aggregate' => 'count',
        'metric' => 18,
    ],
    [
        'for' => CarbonImmutable::now()->subMonths(1)->month,
        'aggregate' => 'count',
        'metric' => 3,
    ],
    [
        'for' => CarbonImmutable::now()->month,
        'aggregate' => 'sum',
        'metric' => 3150,
    ],
    [
        'for' => CarbonImmutable::now()->subMonths(1)->month,
        'aggregate' => 'sum',
        'metric' => 210,
    ],
    [
        'for' => CarbonImmutable::now()->month,
        'aggregate' => 'average',
        'metric' => 175,
    ],
    [
        'for' => CarbonImmutable::now()->subMonths(1)->month,
        'aggregate' => 'average',
        'metric' => 70,
    ],
    [
        'for' => CarbonImmutable::now()->month,
        'aggregate' => 'min',
        'metric' => 90,
    ],
    [
        'for' => CarbonImmutable::now()->subMonths(1)->month,
        'aggregate' => 'min',
        'metric' => 60,
    ],
    [
        'for' => CarbonImmutable::now()->month,
        'aggregate' => 'max',
        'metric' => 260,
    ],
    [
        'for' => CarbonImmutable::now()->subMonths(1)->month,
        'aggregate' => 'max',
        'metric' => 80,
    ],
]);

dataset('aggregate values for year', [
    [
        'for' => CarbonImmutable::now()->year,
        'aggregate' => 'count',
        'metric' => 23,
    ],
    [
        'for' => CarbonImmutable::now()->subYears(1)->year,
        'aggregate' => 'count',
        'metric' => 3,
    ],
    [
        'for' => CarbonImmutable::now()->year,
        'aggregate' => 'sum',
        'metric' => 3450,
    ],
    [
        'for' => CarbonImmutable::now()->subYears(1)->year,
        'aggregate' => 'sum',
        'metric' => 60,
    ],

    [
        'for' => CarbonImmutable::now()->year,
        'aggregate' => 'average',
        'metric' => 150,
    ],
    [
        'for' => CarbonImmutable::now()->subYears(1)->year,
        'aggregate' => 'average',
        'metric' => 20,
    ],

    [
        'for' => CarbonImmutable::now()->year,
        'aggregate' => 'min',
        'metric' => 40,
    ],
    [
        'for' => CarbonImmutable::now()->subYears(1)->year,
        'aggregate' => 'min',
        'metric' => 10,
    ],
    [
        'for' => CarbonImmutable::now()->year,
        'aggregate' => 'max',
        'metric' => 260,
    ],
    [
        'for' => CarbonImmutable::now()->subYears(1)->year,
        'aggregate' => 'max',
        'metric' => 30,
    ],
]);

dataset('previous for day', [
    [
        'for' => 10,
        'aggregate' => 'count',
        'metric' => [
            'value' => 11,
            'previous' => [
                'type' => 'increase',
                'value' => 3,
                'difference' => 8,
                'percentage' => 72.73,
            ],
        ],
    ],
    [
        'for' => 3,
        'aggregate' => 'count',
        'metric' => [
            'value' => 1,
            'previous' => [
                'type' => 'increase',
                'value' => 0,
                'difference' => 1,
                'percentage' => 100,
            ],
        ],
    ],
    [
        'for' => 10,
        'aggregate' => 'sum',
        'metric' => [
            'value' => 2310,
            'previous' => [
                'type' => 'increase',
                'value' => 420,
                'difference' => 1890,
                'percentage' => 81.82,
            ],
        ],
    ],
    [
        'for' => 3,
        'aggregate' => 'sum',
        'metric' => [
            'value' => 90,
            'previous' => [
                'type' => 'increase',
                'value' => 0,
                'difference' => 90,
                'percentage' => 100,
            ],
        ],
    ],
    [
        'for' => 10,
        'aggregate' => 'average',
        'metric' => [
            'value' => 210,
            'previous' => [
                'type' => 'increase',
                'value' => 140,
                'difference' => 70,
                'percentage' => 33.33,
            ],
        ],
    ],
    [
        'for' => 3,
        'aggregate' => 'average',
        'metric' => [
            'value' => 90,
            'previous' => [
                'type' => 'increase',
                'value' => 0,
                'difference' => 90,
                'percentage' => 100,
            ],
        ],
    ],
    [
        'for' => 10,
        'aggregate' => 'min',
        'metric' => [
            'value' => 160,
            'previous' => [
                'type' => 'increase',
                'value' => 130,
                'difference' => 30,
                'percentage' => 18.75,
            ],
        ],
    ],
    [
        'for' => 3,
        'aggregate' => 'min',
        'metric' => [
            'value' => 90,
            'previous' => [
                'type' => 'increase',
                'value' => 0,
                'difference' => 90,
                'percentage' => 100,
            ],
        ],
    ],
    [
        'for' => 10,
        'aggregate' => 'max',
        'metric' => [
            'value' => 260,
            'previous' => [
                'type' => 'increase',
                'value' => 150,
                'difference' => 110,
                'percentage' => 42.31,
            ],
        ],
    ],
    [
        'for' => 3,
        'aggregate' => 'max',
        'metric' => [
            'value' => 90,
            'previous' => [
                'type' => 'increase',
                'value' => 0,
                'difference' => 90,
                'percentage' => 100,
            ],
        ],
    ],
]);

dataset('previous for week', [
    [
        'for' => 15,
        'aggregate' => 'count',
        'metric' => [
            'value' => 16,
            'previous' => [
                'type' => 'increase',
                'value' => 2,
                'difference' => 14,
                'percentage' => 87.5,
            ],
        ],
    ],
    [
        'for' => 14,
        'aggregate' => 'count',
        'metric' => [
            'value' => 2,
            'previous' => [
                'type' => 'increase',
                'value' => 1,
                'difference' => 1,
                'percentage' => 50,
            ],
        ],
    ],
    [
        'for' => 15,
        'aggregate' => 'sum',
        'metric' => [
            'value' => 2960,
            'previous' => [
                'type' => 'increase',
                'value' => 190,
                'difference' => 2770,
                'percentage' => 93.58,
            ],
        ],
    ],
    [
        'for' => 14,
        'aggregate' => 'sum',
        'metric' => [
            'value' => 190,
            'previous' => [
                'type' => 'increase',
                'value' => 80,
                'difference' => 110,
                'percentage' => 57.89,
            ],
        ],
    ],
    [
        'for' => 15,
        'aggregate' => 'average',
        'metric' => [
            'value' => 185,
            'previous' => [
                'type' => 'increase',
                'value' => 95,
                'difference' => 90,
                'percentage' => 48.65,
            ],
        ],
    ],
    [
        'for' => 14,
        'aggregate' => 'average',
        'metric' => [
            'value' => 95,
            'previous' => [
                'type' => 'increase',
                'value' => 80,
                'difference' => 15,
                'percentage' => 15.79,
            ],
        ],
    ],
    [
        'for' => 15,
        'aggregate' => 'min',
        'metric' => [
            'value' => 110,
            'previous' => [
                'type' => 'increase',
                'value' => 90,
                'difference' => 20,
                'percentage' => 18.18,
            ],
        ],
    ],
    [
        'for' => 14,
        'aggregate' => 'min',
        'metric' => [
            'value' => 90,
            'previous' => [
                'type' => 'increase',
                'value' => 80,
                'difference' => 10,
                'percentage' => 11.11,
            ],
        ],
    ],
    [
        'for' => 15,
        'aggregate' => 'max',
        'metric' => [
            'value' => 260,
            'previous' => [
                'type' => 'increase',
                'value' => 100,
                'difference' => 160,
                'percentage' => 61.54,
            ],
        ],
    ],
    [
        'for' => 14,
        'aggregate' => 'max',
        'metric' => [
            'value' => 100,
            'previous' => [
                'type' => 'increase',
                'value' => 80,
                'difference' => 20,
                'percentage' => 20,
            ],
        ],
    ],
]);

dataset('previous for month', [
    [
        'for' => CarbonImmutable::now()->month,
        'aggregate' => 'count',
        'metric' => [
            'value' => 18,
            'previous' => [
                'type' => 'increase',
                'value' => 3,
                'difference' => 15,
                'percentage' => 83.33,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->subMonth()->month,
        'aggregate' => 'count',
        'metric' => [
            'value' => 3,
            'previous' => [
                'type' => 'increase',
                'value' => 1,
                'difference' => 2,
                'percentage' => 66.67,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->month,
        'aggregate' => 'sum',
        'metric' => [
            'value' => 3150,
            'previous' => [
                'type' => 'increase',
                'value' => 210,
                'difference' => 2940,
                'percentage' => 93.33,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->subMonth()->month,
        'aggregate' => 'sum',
        'metric' => [
            'value' => 210,
            'previous' => [
                'type' => 'increase',
                'value' => 50,
                'difference' => 160,
                'percentage' => 76.19,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->month,
        'aggregate' => 'average',
        'metric' => [
            'value' => 175,
            'previous' => [
                'type' => 'increase',
                'value' => 70,
                'difference' => 105,
                'percentage' => 60,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->subMonth()->month,
        'aggregate' => 'average',
        'metric' => [
            'value' => 70,
            'previous' => [
                'type' => 'increase',
                'value' => 50,
                'difference' => 20,
                'percentage' => 28.57,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->month,
        'aggregate' => 'min',
        'metric' => [
            'value' => 90,
            'previous' => [
                'type' => 'increase',
                'value' => 60,
                'difference' => 30,
                'percentage' => 33.33,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->subMonth()->month,
        'aggregate' => 'min',
        'metric' => [
            'value' => 60,
            'previous' => [
                'type' => 'increase',
                'value' => 50,
                'difference' => 10,
                'percentage' => 16.67,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->month,
        'aggregate' => 'max',
        'metric' => [
            'value' => 260,
            'previous' => [
                'type' => 'increase',
                'value' => 80,
                'difference' => 180,
                'percentage' => 69.23,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->subMonth()->month,
        'aggregate' => 'max',
        'metric' => [
            'value' => 80,
            'previous' => [
                'type' => 'increase',
                'value' => 50,
                'difference' => 30,
                'percentage' => 37.5,
            ],
        ],
    ],
]);

dataset('previous for year', [
    [
        'for' => CarbonImmutable::now()->year,
        'aggregate' => 'count',
        'metric' => [
            'value' => 23,
            'previous' => [
                'type' => 'increase',
                'value' => 3,
                'difference' => 20,
                'percentage' => 86.96,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->subYear()->year,
        'aggregate' => 'count',
        'metric' => [
            'value' => 3,
            'previous' => [
                'type' => 'increase',
                'value' => 0,
                'difference' => 3,
                'percentage' => 100,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->year,
        'aggregate' => 'sum',
        'metric' => [
            'value' => 3450,
            'previous' => [
                'type' => 'increase',
                'value' => 60,
                'difference' => 3390,
                'percentage' => 98.26,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->subYear()->year,
        'aggregate' => 'sum',
        'metric' => [
            'value' => 60,
            'previous' => [
                'type' => 'increase',
                'value' => 0,
                'difference' => 60,
                'percentage' => 100,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->year,
        'aggregate' => 'average',
        'metric' => [
            'value' => 150,
            'previous' => [
                'type' => 'increase',
                'value' => 20,
                'difference' => 130,
                'percentage' => 86.67,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->subYear()->year,
        'aggregate' => 'average',
        'metric' => [
            'value' => 20,
            'previous' => [
                'type' => 'increase',
                'value' => 0,
                'difference' => 20,
                'percentage' => 100,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->year,
        'aggregate' => 'min',
        'metric' => [
            'value' => 40,
            'previous' => [
                'type' => 'increase',
                'value' => 10,
                'difference' => 30,
                'percentage' => 75,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->subYear()->year,
        'aggregate' => 'min',
        'metric' => [
            'value' => 10,
            'previous' => [
                'type' => 'increase',
                'value' => 0,
                'difference' => 10,
                'percentage' => 100,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->year,
        'aggregate' => 'max',
        'metric' => [
            'value' => 260,
            'previous' => [
                'type' => 'increase',
                'value' => 30,
                'difference' => 230,
                'percentage' => 88.46,
            ],
        ],
    ],
    [
        'for' => CarbonImmutable::now()->subYear()->year,
        'aggregate' => 'max',
        'metric' => [
            'value' => 30,
            'previous' => [
                'type' => 'increase',
                'value' => 0,
                'difference' => 30,
                'percentage' => 100,
            ],
        ],
    ],
]);
