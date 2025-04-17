<?php

declare(strict_types=1);

use Beacon\Metrics\Concerns\WithInterval;
use Beacon\Metrics\Metrics;

covers(WithInterval::class);

it('calculates by second', function ($db) {
    createTestData($db);

    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $categories = ['category1', 'category2', 'category3'];
    for ($i = 0; $i < 35; $i++) {
        if ($i % 3 === 0) {
            continue;
        }

        $builder->insert([
            'name' => 'Item 1',
            'value' => 10 * $i,
            'category' => $categories[$i % count($categories)],
            'created_at' => now()->subSeconds($i),
            'updated_at' => now()->subSeconds($i),
        ]);
    }

    $value = $metrics->from(now()->subMinutes(2))->count()->bySecond()->trends();

    expect($value->toArray())->toBe([
        'labels' => [
            '2025-04-10 02:36:15',
            '2025-04-10 02:37:15',
            '2025-04-10 02:37:41',
            '2025-04-10 02:37:43',
            '2025-04-10 02:37:44',
            '2025-04-10 02:37:46',
            '2025-04-10 02:37:47',
            '2025-04-10 02:37:49',
            '2025-04-10 02:37:50',
            '2025-04-10 02:37:52',
            '2025-04-10 02:37:53',
            '2025-04-10 02:37:55',
            '2025-04-10 02:37:56',
            '2025-04-10 02:37:58',
            '2025-04-10 02:37:59',
            '2025-04-10 02:38:01',
            '2025-04-10 02:38:02',
            '2025-04-10 02:38:04',
            '2025-04-10 02:38:05',
            '2025-04-10 02:38:07',
            '2025-04-10 02:38:08',
            '2025-04-10 02:38:10',
            '2025-04-10 02:38:11',
            '2025-04-10 02:38:12',
            '2025-04-10 02:38:13',
            '2025-04-10 02:38:14',
            '2025-04-10 02:38:15',
        ],
        'data' => [
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            2,
            1,
            2,
            2,
            1,
        ],
        'total' => 30,
    ], $metrics->query);
})->with('databases');

it('calculates by minute', function ($db) {
    createTestData($db);

    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $categories = ['category1', 'category2', 'category3'];
    for ($i = 0; $i < 35; $i++) {
        if ($i % 3 === 0) {
            continue;
        }

        $builder->insert([
            'name' => 'Item 1',
            'value' => 10 * $i,
            'category' => $categories[$i % count($categories)],
            'created_at' => now()->subMinutes($i),
            'updated_at' => now()->subMinutes($i),
        ]);
    }

    $value = $metrics->from(now()->subMinutes(45))->count()->byMinute()->trends();

    expect($value->toArray())->toBe([
        'labels' => [
            '2025-04-10 02:04',
            '2025-04-10 02:06',
            '2025-04-10 02:07',
            '2025-04-10 02:09',
            '2025-04-10 02:10',
            '2025-04-10 02:12',
            '2025-04-10 02:13',
            '2025-04-10 02:15',
            '2025-04-10 02:16',
            '2025-04-10 02:18',
            '2025-04-10 02:19',
            '2025-04-10 02:21',
            '2025-04-10 02:22',
            '2025-04-10 02:24',
            '2025-04-10 02:25',
            '2025-04-10 02:27',
            '2025-04-10 02:28',
            '2025-04-10 02:30',
            '2025-04-10 02:31',
            '2025-04-10 02:33',
            '2025-04-10 02:34',
            '2025-04-10 02:35',
            '2025-04-10 02:36',
            '2025-04-10 02:37',
            '2025-04-10 02:38',
        ],
        'data' => [
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            2,
            1,
            2,
            2,
            5,
        ],
        'total' => 32,
    ], $metrics->query);
})->with('databases');

it('calculates by hour', function ($db) {
    createTestData($db);

    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $categories = ['category1', 'category2', 'category3'];
    for ($i = 0; $i < 35; $i++) {
        if ($i % 3 === 0) {
            continue;
        }

        $builder->insert([
            'name' => 'Item 1',
            'value' => 10 * $i,
            'category' => $categories[$i % count($categories)],
            'created_at' => now()->subHours($i),
            'updated_at' => now()->subHours($i),
        ]);
    }

    $value = $metrics->from(now()->subHours(45))->count()->byHour()->trends();

    expect($value->toArray())->toBe([
        'labels' => [
            '2025-04-08 16',
            '2025-04-08 18',
            '2025-04-08 19',
            '2025-04-08 21',
            '2025-04-08 22',
            '2025-04-09 00',
            '2025-04-09 01',
            '2025-04-09 02',
            '2025-04-09 03',
            '2025-04-09 04',
            '2025-04-09 06',
            '2025-04-09 07',
            '2025-04-09 09',
            '2025-04-09 10',
            '2025-04-09 12',
            '2025-04-09 13',
            '2025-04-09 15',
            '2025-04-09 16',
            '2025-04-09 18',
            '2025-04-09 19',
            '2025-04-09 21',
            '2025-04-09 22',
            '2025-04-09 23',
            '2025-04-10 00',
            '2025-04-10 01',
            '2025-04-10 02',
        ],
        'data' => [
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            2,
            1,
            2,
            2,
            9,
        ],
        'total' => 37,
    ], $metrics->query);
})->with('databases');

it('calculates by day', function ($db) {
    createTestData($db);

    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $categories = ['category1', 'category2', 'category3'];
    for ($i = 0; $i < 35; $i++) {
        if ($i % 3 === 0) {
            continue;
        }

        $builder->insert([
            'name' => 'Item 1',
            'value' => 10 * $i,
            'category' => $categories[$i % count($categories)],
            'created_at' => now()->subDays($i),
            'updated_at' => now()->subDays($i),
        ]);
    }

    $value = $metrics->from(now()->subDays(45))->count()->byDay()->trends();

    expect($value->toArray())->toBe([
        'labels' => [
            '2025-03-07',
            '2025-03-09',
            '2025-03-10',
            '2025-03-12',
            '2025-03-13',
            '2025-03-15',
            '2025-03-16',
            '2025-03-18',
            '2025-03-19',
            '2025-03-20',
            '2025-03-21',
            '2025-03-22',
            '2025-03-24',
            '2025-03-25',
            '2025-03-27',
            '2025-03-28',
            '2025-03-30',
            '2025-03-31',
            '2025-04-02',
            '2025-04-03',
            '2025-04-05',
            '2025-04-06',
            '2025-04-07',
            '2025-04-08',
            '2025-04-09',
            '2025-04-10',
        ],
        'data' => [
            1,
            1,
            2,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            2,
            1,
            1,
            1,
            1,
            2,
            1,
            2,
            1,
            2,
            4,
            11,
        ],
        'total' => 44,
    ], $metrics->query);
})->with('databases');

it('calculates by day of the week', function ($db) {
    createTestData($db);

    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $categories = ['category1', 'category2', 'category3'];
    for ($i = 0; $i < 35; $i++) {
        if ($i % 3 === 0) {
            continue;
        }

        $builder->insert([
            'name' => 'Item 1',
            'value' => 10 * $i,
            'category' => $categories[$i % count($categories)],
            'created_at' => now()->subDays($i),
            'updated_at' => now()->subDays($i),
        ]);
    }

    $value = $metrics->from(now()->subDays(45))->count()->byDayOfWeek()->trends();

    expect($value->toArray())->toBe([
        'labels' => [
            'Sunday',
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
        ],
        'data' => [
            5,
            5,
            4,
            7,
            17,
            3,
            3,
        ],
        'total' => 44,
    ], $metrics->query);
})->with('databases');

it('calculates by week', function ($db) {
    createTestData($db);

    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $categories = ['category1', 'category2', 'category3'];
    for ($i = 0; $i < 35; $i++) {
        if ($i % 3 === 0) {
            continue;
        }

        $builder->insert([
            'name' => 'Item 1',
            'value' => 10 * $i,
            'category' => $categories[$i % count($categories)],
            'created_at' => now()->subWeeks($i),
            'updated_at' => now()->subWeeks($i),
        ]);
    }

    $value = $metrics->from(now()->subWeeks(45))->count()->byWeek()->trends();

    expect($value->toArray())->toBe([
        'labels' => [
            '2024-W33',
            '2024-W35',
            '2024-W36',
            '2024-W38',
            '2024-W39',
            '2024-W41',
            '2024-W42',
            '2024-W44',
            '2024-W45',
            '2024-W47',
            '2024-W48',
            '2024-W50',
            '2024-W51',
            '2025-W01',
            '2025-W02',
            '2025-W04',
            '2025-W05',
            '2025-W07',
            '2025-W08',
            '2025-W10',
            '2025-W11',
            '2025-W12',
            '2025-W13',
            '2025-W14',
            '2025-W15',
        ],
        'data' => [
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            2,
            1,
            1,
            2,
            1,
            1,
            2,
            1,
            2,
            3,
            16,
        ],
        'total' => 46,
    ], $metrics->query);
})->with('databases');

it('calculates by month', function ($db) {
    createTestData($db);

    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $categories = ['category1', 'category2', 'category3'];
    for ($i = 0; $i < 35; $i++) {
        if ($i % 3 === 0) {
            continue;
        }

        $builder->insert([
            'name' => 'Item 1',
            'value' => 10 * $i,
            'category' => $categories[$i % count($categories)],
            'created_at' => now()->subMonths($i),
            'updated_at' => now()->subMonths($i),
        ]);
    }

    $value = $metrics->from(now()->subMonths(45))->count()->byMonth()->trends();

    expect($value->toArray())->toBe([
        'labels' => [
            '2022-06',
            '2022-08',
            '2022-09',
            '2022-11',
            '2022-12',
            '2023-02',
            '2023-03',
            '2023-05',
            '2023-06',
            '2023-08',
            '2023-09',
            '2023-11',
            '2023-12',
            '2024-02',
            '2024-03',
            '2024-04',
            '2024-05',
            '2024-06',
            '2024-08',
            '2024-09',
            '2024-11',
            '2024-12',
            '2025-01',
            '2025-02',
            '2025-03',
            '2025-04',
        ],
        'data' => [
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            2,
            2,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            2,
            4,
            18,
        ],
        'total' => 49,
    ], $metrics->query);
})->with('databases');

it('calculates by year', function ($db) {
    createTestData($db);

    $builder = \DB::table('test_data');
    $metrics = Metrics::query($builder);

    $categories = ['category1', 'category2', 'category3'];
    for ($i = 0; $i < 35; $i++) {
        if ($i % 3 === 0) {
            continue;
        }

        $builder->insert([
            'name' => 'Item 1',
            'value' => 10 * $i,
            'category' => $categories[$i % count($categories)],
            'created_at' => now()->subYears($i),
            'updated_at' => now()->subYears($i),
        ]);
    }

    $value = $metrics->from(now()->subYears(45))->count()->byYear()->trends();

    expect($value->toArray())->toBe([
        'labels' => [
            '1991',
            '1993',
            '1994',
            '1996',
            '1997',
            '1999',
            '2000',
            '2002',
            '2003',
            '2005',
            '2006',
            '2008',
            '2009',
            '2011',
            '2012',
            '2014',
            '2015',
            '2017',
            '2018',
            '2020',
            '2021',
            '2023',
            '2024',
            '2025',
        ],
        'data' => [
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            4,
            23,
        ],
        'total' => 49,
    ], $metrics->query);
})->with('databases');
