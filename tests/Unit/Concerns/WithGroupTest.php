<?php

declare(strict_types=1);

use Beacon\Metrics\Concerns\WithGroup;
use Beacon\Metrics\Metrics;
use Illuminate\Support\Facades\DB;

covers(WithGroup::class);

it('groups value by column name', function ($db) {
    createTestData($db);

    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $values = $metrics->groupBy('category')->from(now()->subYears(2))->sum('value')->value();

    expect($values->toArray())->toBe([
        'category1' => ['value' => 1170],
        'category2' => ['value' => 1260],
        'category3' => ['value' => 1080],
    ]);
})->with('databases');

it('groups value by expression', function ($db) {
    createTestData($db);

    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $values = $metrics->groupBy(DB::raw('UPPER(category) as CATEGORY'))->from(now()->subYears(2))->sum('value')->value();

    expect($values->toArray())->toBe([
        'CATEGORY1' => ['value' => 1170],
        'CATEGORY2' => ['value' => 1260],
        'CATEGORY3' => ['value' => 1080],
    ]);
})->with('databases');

it('groups trends by column name', function ($db) {
    createTestData($db);

    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $values = $metrics->groupBy('category')->from(now()->subYears(2))->sum('value')->trends();

    expect($values->toArray())->toBe([
        'category1' => [
            'labels' => [
                '2024-02-10',
                '2025-01-10',
                '2025-03-20',
                '2025-04-06',
                '2025-04-09',
                '2025-04-10',
            ],
            'data' => [
                10,
                40,
                70,
                100,
                130,
                820,
            ],
            'total' => 1170,
        ],
        'category2' => [
            'labels' => [
                '2024-03-10',
                '2025-02-10',
                '2025-03-27',
                '2025-04-07',
                '2025-04-09',
                '2025-04-10',
            ],
            'data' => [
                20,
                50,
                80,
                110,
                140,
                860,
            ],
            'total' => 1260,
        ],
        'category3' => [
            'labels' => [
                '2024-04-10',
                '2025-03-10',
                '2025-04-03',
                '2025-04-08',
                '2025-04-09',
                '2025-04-10',
            ],
            'data' => [
                30,
                60,
                90,
                120,
                150,
                630,
            ],
            'total' => 1080,
        ],
    ]);
})->with('databases');
