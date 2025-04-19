<?php

declare(strict_types=1);

use Beacon\Metrics\Exceptions\InvalidDateRangeException;
use Beacon\Metrics\Metrics;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    CarbonImmutable::setTestNow(CarbonImmutable::create(2025, 04, 10, 2, 38, 15));
});

it('calculates value metric no dates', function ($db, $aggregate, $metric) {
    createTestData($db);

    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);
    $value = $metrics->$aggregate('value')->value();

    expect($value)->toBe($metric, $metrics->query);
})->with('databases', 'aggregate values');

it('calculates value metric and previous with decrease', function ($db) {
    createTestData($db);

    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    DB::table('test_data')->insert([
        'name' => 'Item 15',
        'value' => 115,
        'category' => 'category1',
        'created_at' => CarbonImmutable::now()->day(5)->subDay(),
        'updated_at' => CarbonImmutable::now()->day(5)->subDay(),
    ]);

    $value = $metrics->sum('value')->between(CarbonImmutable::now()->day(5)->startOfDay(), CarbonImmutable::now()->day(5)->endOfDay())->withPrevious()->value();

    expect($value->toArray())->toBe([
        'value' => 0,
        'previous' => [
            'type' => 'decrease',
            'value' => 115,
            'difference' => 115,
            'percentage' => 100,
        ],
    ], $metrics->query);
})->with('databases');

it('casts floats correctly', function ($db) {
    createTestData($db);

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
})->with('databases');

it('calculates value over all data', function ($db) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $value = $metrics->count()->all()->value();

    expect($value)
        ->toBe(26);
})->with('databases');

it('calculates value over all data with missing', function ($db) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    $metrics->count()->all()->withPrevious()->value();
})->with('databases')->throws(InvalidDateRangeException::class);

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

    $value = $metrics->all()->min('value')->byDay()->value();
    expect($value)->toBe(110, $metrics->query);
})->with('databases');

it('can use model queries with group', function ($db) {
    createTestData($db);

    $model = new class extends Model
    {
        protected $table = 'test_data';
    };

    $metrics = Metrics::query(
        $model::query()
            ->where('value', '>', 100)
    );

    $value = $metrics->all()->min('value')->byDay()->groupBy('category')->value();
    expect($value->toArray())->toBe([
        'category1' => 130,
        'category2' => 110,
        'category3' => 120,
    ], $metrics->query);
})->with('databases');

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
