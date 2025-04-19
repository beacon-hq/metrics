<?php

declare(strict_types=1);

use Beacon\Metrics\Metrics;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    CarbonImmutable::setTestNow(CarbonImmutable::create(2025, 04, 10, 2, 38, 15));
});

it('calculates daily trends by default', function ($db, $aggregate, $expected) {
    createTestData($db);

    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->$aggregate('value')->trends();

    expect($trends->toArray())->toBe($expected, $metrics->query);
})->with('databases', 'daily trends');

it('calculates daily trends by default with missing data', function ($db, $aggregate, $expected) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->fillMissing()->$aggregate('value')->trends();

    expect($trends->toArray())->toBe($expected, $metrics->query);
})->with('databases', 'daily trends with missing data');

it('calculates daily trends', function ($db, $aggregate, $expected) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->$aggregate('value')->byDay()->trends();

    expect($trends->toArray())->toBe($expected);
})->with('databases', 'daily trends');

it('calculates daily trends with missing data', function ($db, $aggregate, $expected) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->fillMissing()->$aggregate('value')->byDay()->trends();

    expect($trends->toArray())->toBe($expected);
})->with('databases', 'daily trends with missing data');

it('calculates weekly trends', function ($db, $aggregate, $expected) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->$aggregate('value')->byWeek()->trends();

    expect($trends->toArray())->toBe($expected, $metrics->query);
})->with('databases', 'weekly trends');

it('calculates weekly trends with missing data', function ($db, $aggregate, $expected) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->fillMissing()->$aggregate('value')->byWeek()->trends();

    expect($trends->toArray())->toBe($expected, $metrics->query);
})->with('databases', 'weekly trends with missing data');

it('calculates monthly trends', function ($db, $aggregate, $expected) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->$aggregate('value')->between(now()->subDays(90), now())->byMonth()->trends();

    expect($trends->toArray())->toBe($expected, $metrics->query);
})->with('databases', 'monthly trends');

it('calculates monthly trends with missing data', function ($db, $aggregate, $expected) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->fillMissing()->$aggregate('value')->between(now()->subDays(180), now())->byMonth()->trends();

    expect($trends->toArray())->toBe($expected, $metrics->query);
})->with('databases', 'monthly trends with missing data');

it('calculates yearly trends', function ($db, $aggregate, $expected) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->$aggregate('value')->between(now()->subYear(3), now())->byYear()->trends();

    expect($trends->toArray())->toBe($expected, $metrics->query);
})->with('databases', 'yearly trends');

it('calculates yearly trends with missing data', function ($db, $aggregate, $expected) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->fillMissing()->$aggregate('value')->between(now()->subYear(3), now())->byYear()->trends();

    expect($trends->toArray())->toBe($expected, $metrics->query);
})->with('databases', 'yearly trends with missing data');

it('calculates daily trends every 3 days', function ($db, $aggregate, $expected) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->$aggregate('value')->from(now()->startOfMonth())->byDay(3)->trends();

    expect($trends->toArray())->toBe($expected, $metrics->query);
})->with('databases', 'every third day trends');

it('calculates daily trends every 3 days with missing data', function ($db, $aggregate, $expected) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->fillMissing()->$aggregate('value')->from(now()->startOfMonth())->byDay(3)->trends();

    expect($trends->toArray())->toBe($expected);
})->with('databases', 'every third day trends with missing data');

it('calculates daily trends every other month', function ($db, $aggregate, $expected) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->$aggregate('value')->from(now()->subMonths(8))->byMonth(2)->trends();

    expect($trends->toArray())->toBe($expected, $metrics->query);
})->with('databases', 'every other month trends');

it('calculates daily trends every other month with missing data', function ($db, $aggregate, $expected) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->fillMissing()->$aggregate('value')->from(now()->subMonths(8))->byMonth(2)->trends();

    expect($trends->toArray())->toBe($expected, $metrics->query);
})->with('databases', 'every other month trends with missing data');

it('calculates daily trends over all data', function ($db) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->count()->all()->byDay()->trends();

    expect($trends->get('labels'))
        ->toHaveCount(14)
        ->and($trends->get('total'))
        ->toBe(26);
})->with('databases');

it('calculates daily trends over all data with missing', function ($db) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->count()->all()->fillMissing()->byDay()->trends();

    expect($trends->get('labels'))
        ->toHaveCount(426)
        ->and($trends->get('total'))
        ->toBe(26);
})->with('databases');

it('casts floats correctly', function ($db) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $builder->insert([
        [
            'name' => 'Item 1',
            'value' => 60.123456,
            'category' => 'category1',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ],
        [
            'name' => 'Item 2',
            'value' => 70.654321,
            'category' => 'category2',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ],
        [
            'name' => 'Item 3',
            'value' => 80.987654,
            'category' => 'category3',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
        ],
    ]);

    $trends = $metrics->sum('value')->from(CarbonImmutable::now()->subDays(7))->byDay()->trends();

    expect($trends->toArray())->toBe([
        'labels' => [
            '2025-04-03',
            '2025-04-06',
            '2025-04-07',
            '2025-04-08',
            '2025-04-09',
            '2025-04-10',
        ],
        'data' => [
            90,
            100,
            110,
            120,
            420,
            2521.7654,
        ],
        'total' => 3361.7654,
    ], $metrics->query);
})->with('databases');

it('returns percentage values', function ($db) {
    createTestData($db);

    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $trends = $metrics->sum('value')->from(CarbonImmutable::now()->subDays(7))->byDay()->trends(true);

    expect($trends->toArray())->toBe([
        'labels' => [
            '2025-04-03',
            '2025-04-06',
            '2025-04-07',
            '2025-04-08',
            '2025-04-09',
            '2025-04-10',
        ],
        'data' => [
            2.86, // 90,
            3.17, // 100,
            3.49, // 110,
            3.81, // 120,
            13.33, // 420,
            73.33, // 2310,
        ],
        'total' => 3150,
    ], $metrics->query);
})->with('databases');

it('can use model queries', function ($db) {
    createTestData($db);

    $model = new class extends Model
    {
        protected $table = 'test_data';
    };

    $metrics = Metrics::query(
        $model::query()
            ->where('value', '>', 100)
    );

    expect($metrics->all()->min('value')->byDay()->trends()->get('labels'))->toHaveCount(4);
})->with('databases');

dataset('daily trends', [
    [
        'aggregate' => 'count',
        'expected' => [
            'labels' => [
                '2025-03-10',
                '2025-03-20',
                '2025-03-27',
            ],
            'data' => [1, 1, 1],
            'total' => 3,
        ],
    ],
    [
        'aggregate' => 'sum',
        'expected' => [
            'labels' => [
                '2025-03-10',
                '2025-03-20',
                '2025-03-27',
            ],
            'data' => [60, 70, 80],
            'total' => 210,
        ],
    ],
    [
        'aggregate' => 'average',
        'expected' => [
            'labels' => [
                '2025-03-10',
                '2025-03-20',
                '2025-03-27',
            ],
            'data' => [60, 70, 80],
            'total' => 70,
        ],
    ],
    [
        'aggregate' => 'min',
        'expected' => [
            'labels' => [
                '2025-03-10',
                '2025-03-20',
                '2025-03-27',
            ],
            'data' => [60, 70, 80],
            'total' => 60,
        ],
    ],
    [
        'aggregate' => 'max',
        'expected' => [
            'labels' => [
                '2025-03-10',
                '2025-03-20',
                '2025-03-27',
            ],
            'data' => [60, 70, 80],
            'total' => 80,
        ],
    ],
]);

dataset('daily trends with missing data', [
    [
        'aggregate' => 'count',
        'expected' => [
            'labels' => [
                '2025-03-01',
                '2025-03-02',
                '2025-03-03',
                '2025-03-04',
                '2025-03-05',
                '2025-03-06',
                '2025-03-07',
                '2025-03-08',
                '2025-03-09',
                '2025-03-10',
                '2025-03-11',
                '2025-03-12',
                '2025-03-13',
                '2025-03-14',
                '2025-03-15',
                '2025-03-16',
                '2025-03-17',
                '2025-03-18',
                '2025-03-19',
                '2025-03-20',
                '2025-03-21',
                '2025-03-22',
                '2025-03-23',
                '2025-03-24',
                '2025-03-25',
                '2025-03-26',
                '2025-03-27',
                '2025-03-28',
                '2025-03-29',
                '2025-03-30',
                '2025-03-31',
            ],
            'data' => [
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                1,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                1,
                0,
                0,
                0,
                0,
                0,
                0,
                1,
                0,
                0,
                0,
                0,
            ],
            'total' => 3,
        ],
    ],
    [
        'aggregate' => 'sum',
        'expected' => [
            'labels' => [
                '2025-03-01',
                '2025-03-02',
                '2025-03-03',
                '2025-03-04',
                '2025-03-05',
                '2025-03-06',
                '2025-03-07',
                '2025-03-08',
                '2025-03-09',
                '2025-03-10',
                '2025-03-11',
                '2025-03-12',
                '2025-03-13',
                '2025-03-14',
                '2025-03-15',
                '2025-03-16',
                '2025-03-17',
                '2025-03-18',
                '2025-03-19',
                '2025-03-20',
                '2025-03-21',
                '2025-03-22',
                '2025-03-23',
                '2025-03-24',
                '2025-03-25',
                '2025-03-26',
                '2025-03-27',
                '2025-03-28',
                '2025-03-29',
                '2025-03-30',
                '2025-03-31',
            ],
            'data' => [
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                60,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                70,
                0,
                0,
                0,
                0,
                0,
                0,
                80,
                0,
                0,
                0,
                0,
            ],
            'total' => 210,
        ],
    ],
    [
        'aggregate' => 'average',
        'expected' => [
            'labels' => [
                '2025-03-01',
                '2025-03-02',
                '2025-03-03',
                '2025-03-04',
                '2025-03-05',
                '2025-03-06',
                '2025-03-07',
                '2025-03-08',
                '2025-03-09',
                '2025-03-10',
                '2025-03-11',
                '2025-03-12',
                '2025-03-13',
                '2025-03-14',
                '2025-03-15',
                '2025-03-16',
                '2025-03-17',
                '2025-03-18',
                '2025-03-19',
                '2025-03-20',
                '2025-03-21',
                '2025-03-22',
                '2025-03-23',
                '2025-03-24',
                '2025-03-25',
                '2025-03-26',
                '2025-03-27',
                '2025-03-28',
                '2025-03-29',
                '2025-03-30',
                '2025-03-31',
            ],
            'data' => [
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                60,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                70,
                0,
                0,
                0,
                0,
                0,
                0,
                80,
                0,
                0,
                0,
                0,
            ],
            'total' => 70,
        ],
    ],
    [
        'aggregate' => 'min',
        'expected' => [
            'labels' => [
                '2025-03-01',
                '2025-03-02',
                '2025-03-03',
                '2025-03-04',
                '2025-03-05',
                '2025-03-06',
                '2025-03-07',
                '2025-03-08',
                '2025-03-09',
                '2025-03-10',
                '2025-03-11',
                '2025-03-12',
                '2025-03-13',
                '2025-03-14',
                '2025-03-15',
                '2025-03-16',
                '2025-03-17',
                '2025-03-18',
                '2025-03-19',
                '2025-03-20',
                '2025-03-21',
                '2025-03-22',
                '2025-03-23',
                '2025-03-24',
                '2025-03-25',
                '2025-03-26',
                '2025-03-27',
                '2025-03-28',
                '2025-03-29',
                '2025-03-30',
                '2025-03-31',
            ],
            'data' => [
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                60,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                70,
                0,
                0,
                0,
                0,
                0,
                0,
                80,
                0,
                0,
                0,
                0,
            ],
            'total' => 60,
        ],
    ],
    [
        'aggregate' => 'max',
        'expected' => [
            'labels' => [
                '2025-03-01',
                '2025-03-02',
                '2025-03-03',
                '2025-03-04',
                '2025-03-05',
                '2025-03-06',
                '2025-03-07',
                '2025-03-08',
                '2025-03-09',
                '2025-03-10',
                '2025-03-11',
                '2025-03-12',
                '2025-03-13',
                '2025-03-14',
                '2025-03-15',
                '2025-03-16',
                '2025-03-17',
                '2025-03-18',
                '2025-03-19',
                '2025-03-20',
                '2025-03-21',
                '2025-03-22',
                '2025-03-23',
                '2025-03-24',
                '2025-03-25',
                '2025-03-26',
                '2025-03-27',
                '2025-03-28',
                '2025-03-29',
                '2025-03-30',
                '2025-03-31',
            ],
            'data' => [
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                60,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                0,
                70,
                0,
                0,
                0,
                0,
                0,
                0,
                80,
                0,
                0,
                0,
                0,
            ],
            'total' => 80,
        ],
    ],
]);

dataset('weekly trends', [
    [
        'aggregate' => 'count',
        'expected' => [
            'labels' => [
                '2025-W11',
                '2025-W12',
                '2025-W13',
                '2025-W14',
            ],
            'data' => [1, 1, 1, 2],
            'total' => 5,
        ],
    ],
    // [
    //     'aggregate' => 'sum',
    //     'expected' => [
    //         'labels' => [
    //             '2025-W11',
    //             '2025-W12',
    //             '2025-W13',
    //             '2025-W14',
    //         ],
    //         'data' => [60, 70, 80, 190],
    //         'total' => 400,
    //     ],
    // ],
    // [
    //     'aggregate' => 'average',
    //     'expected' => [
    //         'labels' => [
    //             '2025-W11',
    //             '2025-W12',
    //             '2025-W13',
    //             '2025-W14',
    //         ],
    //         'data' => [60, 70, 80, 95],
    //         'total' => 80,
    //     ],
    // ],
    // [
    //     'aggregate' => 'min',
    //     'expected' => [
    //         'labels' => [
    //             '2025-W11',
    //             '2025-W12',
    //             '2025-W13',
    //             '2025-W14',
    //         ],
    //         'data' => [60, 70, 80, 90],
    //         'total' => 60,
    //     ],
    // ],
    // [
    //     'aggregate' => 'max',
    //     'expected' => [
    //         'labels' => [
    //             '2025-W11',
    //             '2025-W12',
    //             '2025-W13',
    //             '2025-W14',
    //         ],
    //         'data' => [60, 70, 80, 100],
    //         'total' => 100,
    //     ],
    // ],
]);

dataset('weekly trends with missing data', [
    [
        'aggregate' => 'count',
        'expected' => [
            'labels' => [
                '2025-W09',
                '2025-W10',
                '2025-W11',
                '2025-W12',
                '2025-W13',
                '2025-W14',
            ],
            'data' => [
                0,
                0,
                1,
                1,
                1,
                2,
            ],
            'total' => 5,
        ],
    ],
    [
        'aggregate' => 'sum',
        'expected' => [
            'labels' => [
                '2025-W09',
                '2025-W10',
                '2025-W11',
                '2025-W12',
                '2025-W13',
                '2025-W14',
            ],
            'data' => [
                0,
                0,
                60,
                70,
                80,
                190,
            ],
            'total' => 400,
        ],
    ],
    [
        'aggregate' => 'average',
        'expected' => [
            'labels' => [
                '2025-W09',
                '2025-W10',
                '2025-W11',
                '2025-W12',
                '2025-W13',
                '2025-W14',
            ],
            'data' => [
                0,
                0,
                60,
                70,
                80,
                95,
            ],
            'total' => 80,
        ],
    ],
    [
        'aggregate' => 'min',
        'expected' => [
            'labels' => [
                '2025-W09',
                '2025-W10',
                '2025-W11',
                '2025-W12',
                '2025-W13',
                '2025-W14',
            ],
            'data' => [
                0,
                0,
                60,
                70,
                80,
                90,
            ],
            'total' => 60,
        ],
    ],
    [
        'aggregate' => 'max',
        'expected' => [
            'labels' => [
                '2025-W09',
                '2025-W10',
                '2025-W11',
                '2025-W12',
                '2025-W13',
                '2025-W14',
            ],
            'data' => [

                0,
                0,
                60,
                70,
                80,
                100,
            ],
            'total' => 100,
        ],
    ],
]);

dataset('monthly trends', [
    [
        'aggregate' => 'count',
        'expected' => [
            'labels' => [
                '2025-01',
                '2025-02',
                '2025-03',
                '2025-04',
            ],
            'data' => [
                1,
                1,
                3,
                18,
            ],
            'total' => 23,
        ],
    ],
    [
        'aggregate' => 'sum',
        'expected' => [
            'labels' => [
                '2025-01',
                '2025-02',
                '2025-03',
                '2025-04',
            ],
            'data' => [
                40,
                50,
                210,
                3150,
            ],
            'total' => 3450,
        ],
    ],
    [
        'aggregate' => 'average',
        'expected' => [
            'labels' => [
                '2025-01',
                '2025-02',
                '2025-03',
                '2025-04',
            ],
            'data' => [
                40,
                50,
                70,
                175,
            ],
            'total' => 150,
        ],
    ],
    [
        'aggregate' => 'min',
        'expected' => [
            'labels' => [
                '2025-01',
                '2025-02',
                '2025-03',
                '2025-04',
            ],
            'data' => [
                40,
                50,
                60,
                90,
            ],
            'total' => 40,
        ],
    ],
    [
        'aggregate' => 'max',
        'expected' => [
            'labels' => [
                '2025-01',
                '2025-02',
                '2025-03',
                '2025-04',
            ],
            'data' => [
                40,
                50,
                80,
                260,
            ],
            'total' => 260,
        ],
    ],
]);

dataset('monthly trends with missing data', [
    [
        'aggregate' => 'count',
        'expected' => [
            'labels' => [
                '2024-10',
                '2024-11',
                '2024-12',
                '2025-01',
                '2025-02',
                '2025-03',
                '2025-04',
            ],
            'data' => [0, 0, 0, 1, 1, 3, 18],
            'total' => 23,
        ],
    ],
    [
        'aggregate' => 'sum',
        'expected' => [
            'labels' => [
                '2024-10',
                '2024-11',
                '2024-12',
                '2025-01',
                '2025-02',
                '2025-03',
                '2025-04',
            ],
            'data' => [0, 0, 0, 40, 50, 210, 3150],
            'total' => 3450,
        ],
    ],
    [
        'aggregate' => 'average',
        'expected' => [
            'labels' => [
                '2024-10',
                '2024-11',
                '2024-12',
                '2025-01',
                '2025-02',
                '2025-03',
                '2025-04',
            ],
            'data' => [0, 0, 0, 40, 50, 70, 175],
            'total' => 150,
        ],
    ],
    [
        'aggregate' => 'min',
        'expected' => [
            'labels' => [
                '2024-10',
                '2024-11',
                '2024-12',
                '2025-01',
                '2025-02',
                '2025-03',
                '2025-04',
            ],
            'data' => [0, 0, 0, 40, 50, 60, 90],
            'total' => 40,
        ],
    ],
    [
        'aggregate' => 'max',
        'expected' => [
            'labels' => [
                '2024-10',
                '2024-11',
                '2024-12',
                '2025-01',
                '2025-02',
                '2025-03',
                '2025-04',
            ],
            'data' => [0, 0, 0, 40, 50, 80, 260],
            'total' => 260,
        ],
    ],
]);

dataset('yearly trends', [
    [
        'aggregate' => 'count',
        'expected' => [
            'labels' => [
                '2024',
                '2025',
            ],
            'data' => [3, 23],
            'total' => 26,
        ],
    ],
    [
        'aggregate' => 'sum',
        'expected' => [
            'labels' => [
                '2024',
                '2025',
            ],
            'data' => [60, 3450],
            'total' => 3510,
        ],
    ],
    [
        'aggregate' => 'average',
        'expected' => [
            'labels' => [
                '2024',
                '2025',
            ],
            'data' => [20, 150],
            'total' => 135,
        ],
    ],
    [
        'aggregate' => 'min',
        'expected' => [
            'labels' => [
                '2024',
                '2025',
            ],
            'data' => [10, 40],
            'total' => 10,
        ],
    ],
    [
        'aggregate' => 'max',
        'expected' => [
            'labels' => [
                '2024',
                '2025',
            ],
            'data' => [30, 260],
            'total' => 260,
        ],
    ],
]);

dataset('yearly trends with missing data', [
    [
        'aggregate' => 'count',
        'expected' => [
            'labels' => [
                '2022',
                '2023',
                '2024',
                '2025',
            ],
            'data' => [0, 0, 3, 23],
            'total' => 26,
        ],
    ],
    [
        'aggregate' => 'sum',
        'expected' => [
            'labels' => [
                '2022',
                '2023',
                '2024',
                '2025',
            ],
            'data' => [0, 0, 60, 3450],
            'total' => 3510,
        ],
    ],
    [
        'aggregate' => 'average',
        'expected' => [
            'labels' => [
                '2022',
                '2023',
                '2024',
                '2025',
            ],
            'data' => [0, 0, 20, 150],
            'total' => 135,
        ],
    ],
    [
        'aggregate' => 'min',
        'expected' => [
            'labels' => [
                '2022',
                '2023',
                '2024',
                '2025',
            ],
            'data' => [0, 0, 10, 40],
            'total' => 10,
        ],
    ],
    [
        'aggregate' => 'max',
        'expected' => [
            'labels' => [
                '2022',
                '2023',
                '2024',
                '2025',
            ],
            'data' => [0, 0, 30, 260],
            'total' => 260,
        ],
    ],
]);

dataset('every third day trends', [
    [
        'aggregate' => 'count',
        'expected' => [
            'labels' => [
                '2025-04-01',
                '2025-04-04',
                '2025-04-07',
                '2025-04-10',
            ],
            'data' => [
                1,
                1,
                5,
                11,
            ],
            'total' => 18,
        ],
    ],
    [
        'aggregate' => 'sum',
        'expected' => [
            'labels' => [
                '2025-04-01',
                '2025-04-04',
                '2025-04-07',
                '2025-04-10',
            ],
            'data' => [
                90,
                100,
                650,
                2310,
            ],
            'total' => 3150,
        ],
    ],
    [
        'aggregate' => 'average',
        'expected' => [
            'labels' => [
                '2025-04-01',
                '2025-04-04',
                '2025-04-07',
                '2025-04-10',
            ],
            'data' => [
                90,
                100,
                130,
                210,
            ],
            'total' => 175,
        ],
    ],
    [
        'aggregate' => 'min',
        'expected' => [
            'labels' => [
                '2025-04-01',
                '2025-04-04',
                '2025-04-07',
                '2025-04-10',
            ],
            'data' => [
                90,
                100,
                110,
                160,
            ],
            'total' => 90,
        ],
    ],
    [
        'aggregate' => 'max',
        'expected' => [
            'labels' => [
                '2025-04-01',
                '2025-04-04',
                '2025-04-07',
                '2025-04-10',
            ],
            'data' => [
                90,
                100,
                150,
                260,
            ],
            'total' => 260,
        ],
    ],
]);

dataset('every third day trends with missing data', [
    [
        'aggregate' => 'count',
        'expected' => [
            'labels' => [
                '2025-04-01',
                '2025-04-04',
                '2025-04-07',
                '2025-04-10',
            ],
            'data' => [
                1,
                1,
                5,
                11,
            ],
            'total' => 18,
        ],
    ],
    [
        'aggregate' => 'sum',
        'expected' => [
            'labels' => [
                '2025-04-01',
                '2025-04-04',
                '2025-04-07',
                '2025-04-10',
            ],
            'data' => [
                90,
                100,
                650,
                2310,
            ],
            'total' => 3150,
        ],
    ],
    [
        'aggregate' => 'average',
        'expected' => [
            'labels' => [
                '2025-04-01',
                '2025-04-04',
                '2025-04-07',
                '2025-04-10',
            ],
            'data' => [
                90,
                100,
                130,
                210,
            ],
            'total' => 175,
        ],
    ],
    [
        'aggregate' => 'min',
        'expected' => [
            'labels' => [
                '2025-04-01',
                '2025-04-04',
                '2025-04-07',
                '2025-04-10',
            ],
            'data' => [
                90,
                100,
                110,
                160,
            ],
            'total' => 90,
        ],
    ],
    [
        'aggregate' => 'max',
        'expected' => [
            'labels' => [
                '2025-04-01',
                '2025-04-04',
                '2025-04-07',
                '2025-04-10',
            ],
            'data' => [
                90,
                100,
                150,
                260,
            ],
            'total' => 260,
        ],
    ],
]);

dataset('every other month trends', [
    [
        'aggregate' => 'count',
        'expected' => [
            'labels' => [
                '2024-12',
                '2025-02',
                '2025-04',
            ],
            'data' => [
                1,
                4,
                18,
            ],
            'total' => 23,
        ],
    ],
    [
        'aggregate' => 'sum',
        'expected' => [
            'labels' => [
                '2024-12',
                '2025-02',
                '2025-04',
            ],
            'data' => [
                40,
                260,
                3150,
            ],
            'total' => 3450,
        ],
    ],
    [
        'aggregate' => 'average',
        'expected' => [
            'labels' => [
                '2024-12',
                '2025-02',
                '2025-04',
            ],
            'data' => [
                40,
                65,
                175,
            ],
            'total' => 150,
        ],
    ],
    [
        'aggregate' => 'min',
        'expected' => [
            'labels' => [
                '2024-12',
                '2025-02',
                '2025-04',
            ],
            'data' => [
                40,
                50,
                90,
            ],
            'total' => 40,
        ],
    ],
    [
        'aggregate' => 'max',
        'expected' => [
            'labels' => [
                '2024-12',
                '2025-02',
                '2025-04',
            ],
            'data' => [
                40,
                80,
                260,
            ],
            'total' => 260,
        ],
    ],
]);

dataset('every other month trends with missing data', [
    [
        'aggregate' => 'count',
        'expected' => [
            'labels' => [
                '2024-08',
                '2024-10',
                '2024-12',
                '2025-02',
                '2025-04',
            ],
            'data' => [
                0,
                0,
                1,
                4,
                18,
            ],
            'total' => 23,
        ],
    ],
    [
        'aggregate' => 'sum',
        'expected' => [
            'labels' => [
                '2024-08',
                '2024-10',
                '2024-12',
                '2025-02',
                '2025-04',
            ],
            'data' => [
                0,
                0,
                40,
                260,
                3150,
            ],
            'total' => 3450,
        ],
    ],
    [
        'aggregate' => 'average',
        'expected' => [
            'labels' => [
                '2024-08',
                '2024-10',
                '2024-12',
                '2025-02',
                '2025-04',
            ],
            'data' => [
                0,
                0,
                40,
                65,
                175,
            ],
            'total' => 150,
        ],
    ],
    [
        'aggregate' => 'min',
        'expected' => [
            'labels' => [
                '2024-08',
                '2024-10',
                '2024-12',
                '2025-02',
                '2025-04',
            ],
            'data' => [
                0,
                0,
                40,
                50,
                90,
            ],
            'total' => 40,
        ],
    ],
    [
        'aggregate' => 'max',
        'expected' => [
            'labels' => [
                '2024-08',
                '2024-10',
                '2024-12',
                '2025-02',
                '2025-04',
            ],
            'data' => [
                0,
                0,
                40,
                80,
                260,
            ],
            'total' => 260,
        ],
    ],
]);
