<?php

declare(strict_types=1);

use Beacon\Metrics\Concerns\WithProjection;
use Beacon\Metrics\Metrics;
use Beacon\Metrics\Values\Collections\TrendMetricCollection;
use Beacon\Metrics\Values\Projections;
use Beacon\Metrics\Values\Projections\DateProjection;
use Beacon\Metrics\Values\Projections\WhenProjection;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

covers(WithProjection::class);

beforeEach(function () {
    CarbonImmutable::setTestNow(CarbonImmutable::create(2025, 04, 10, 2, 38, 15));
});

it('can project when a metric will reach a specific value', function ($db) {
    createTestSchema($db);

    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    DB::table('test_data')->insert([
        [
            'name' => 'Item 15',
            'value' => 100,
            'category' => 'category1',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ],
        [
            'name' => 'Item 16',
            'value' => 110,
            'category' => 'category1',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ],
        [
            'name' => 'Item 17',
            'value' => 120,
            'category' => 'category1',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ],
        [
            'name' => 'Item 18',
            'value' => 130,
            'category' => 'category1',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $trends = $metrics->sum('value')
        ->between(now()->subDays(3), now())
        ->byDay()
        ->projectWhen(2000)
        ->trends();

    expect($trends)
        ->toHaveKey('projections')
        ->and($trends->projections)
        ->toBeInstanceOf(Projections::class)
        ->and($trends->projections->when)
        ->toBeInstanceOf(WhenProjection::class)
        ->and($trends->projections->when->targetValue)
        ->toBe(2000)
        ->and($trends->projections->when->projectedDate->toDateTimeString())
        ->toBe('2025-04-24 00:00:00')
        ->and($trends->projections->when->confidence)
        ->toBe(76);
})->with('databases');

it('can project what a metric will be at a specific date', function ($db) {
    createTestSchema($db);

    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    DB::table('test_data')->insert([
        [
            'name' => 'Item 15',
            'value' => 100,
            'category' => 'category1',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ],
        [
            'name' => 'Item 16',
            'value' => 110,
            'category' => 'category1',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ],
        [
            'name' => 'Item 17',
            'value' => 120,
            'category' => 'category1',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ],
        [
            'name' => 'Item 18',
            'value' => 130,
            'category' => 'category1',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $targetDate = now()->addDays(5);
    $trends = $metrics->sum('value')
        ->between(now()->subDays(3), now())
        ->byDay()
        ->projectForDate($targetDate)
        ->trends();

    expect($trends)
        ->toHaveKey('projections')
        ->and($trends->projections)->toHaveKey('date')
        ->and($trends->projections->date)->toBeInstanceOf(DateProjection::class)
        ->and($trends->projections->date->targetDate->equalTo($targetDate))->toBeTrue()
        ->and($trends->projections->date->projectedTotal)->toBe(984.0)
        ->and($trends->projections->date->confidence)->toBe(76);
})->with('databases');

it('can chain multiple projections', function ($db) {
    createTestSchema($db);

    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    DB::table('test_data')->insert([
        [
            'name' => 'Item 15',
            'value' => 100,
            'category' => 'category1',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ],
        [
            'name' => 'Item 16',
            'value' => 110,
            'category' => 'category1',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ],
        [
            'name' => 'Item 17',
            'value' => 120,
            'category' => 'category1',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ],
        [
            'name' => 'Item 18',
            'value' => 130,
            'category' => 'category1',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $targetDate = now()->endOfMonth();
    $trends = $metrics->sum('value')
        ->between(now()->subDays(3), now())
        ->byDay()
        ->projectWhen(2000)
        ->projectForDate($targetDate)
        ->trends();

    expect($trends)
        ->toHaveKey('projections')
        ->and($trends->projections)->toBeInstanceOf(Projections::class)
        ->and($trends->projections->when->projectedDate->toDateTimeString())->toBe('2025-04-24 00:00:00')
        ->and($trends->projections->date->projectedTotal)->toBe(2200.0);
})->with('databases');

it('handles grouped projections', function ($db) {
    createTestSchema($db);

    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    DB::table('test_data')->insert([
        [
            'name' => 'Item 15',
            'value' => 100,
            'category' => 'category1',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ],
        [
            'name' => 'Item 16',
            'value' => 110,
            'category' => 'category1',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ],
        [
            'name' => 'Item 17',
            'value' => 120,
            'category' => 'category1',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ],
        [
            'name' => 'Item 18',
            'value' => 130,
            'category' => 'category1',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Item 19',
            'value' => 200,
            'category' => 'category2',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ],
        [
            'name' => 'Item 20',
            'value' => 220,
            'category' => 'category2',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ],
        [
            'name' => 'Item 21',
            'value' => 240,
            'category' => 'category2',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ],
        [
            'name' => 'Item 22',
            'value' => 260,
            'category' => 'category2',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $targetDate = now()->addDays(5);
    $trends = $metrics->sum('value')
        ->between(now()->subDays(3), now())
        ->byDay()
        ->groupBy('category')
        ->projectWhen(2000)
        ->projectForDate($targetDate)
        ->trends();

    expect($trends)
        ->toBeInstanceOf(TrendMetricCollection::class)
        ->and($trends->has('category1'))->toBeTrue()
        ->and($trends['category1']->projections)->toBeInstanceOf(Projections::class)
        ->and($trends['category1']->projections->when->projectedDate->toDateTimeString())->toBe('2025-04-24 00:00:00')
        ->and($trends['category1']->projections->date->projectedTotal)->toBe(984.0)
        ->and($trends->has('category2'))->toBeTrue()
        ->and($trends['category2']->projections->when->projectedDate->toDateTimeString())->toBe('2025-04-15 00:00:00')
        ->and($trends['category2']->projections->date->projectedTotal)->toBe(1967.0);
})->with('databases');

it('can project when a metric will reach a specific value with slow rate of change', function ($db) {
    createTestSchema($db);

    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    DB::table('test_data')->insert([
        [
            'name' => 'Item 15',
            'value' => 50,
            'category' => 'category1',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ],
        [
            'name' => 'Item 16',
            'value' => 10,
            'category' => 'category1',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ],
        [
            'name' => 'Item 17',
            'value' => 10,
            'category' => 'category1',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ],
        [
            'name' => 'Item 18',
            'value' => 10,
            'category' => 'category1',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Item 19',
            'value' => 5,
            'category' => 'category1',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Item 20',
            'value' => 5,
            'category' => 'category1',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Item 21',
            'value' => 10,
            'category' => 'category1',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Item 22',
            'value' => 5,
            'category' => 'category1',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $trends = $metrics->sum('value')
        ->between(now()->subDays(3), now())
        ->byDay()
        ->projectWhen(1000)
        ->trends();

    expect($trends)
        ->toHaveKey('projections')
        ->and($trends->projections)
        ->toBeInstanceOf(Projections::class)
        ->and($trends->projections->when)
        ->toBeInstanceOf(WhenProjection::class)
        ->and($trends->projections->when->projectedDate->toDateTimeString())
        ->toBe('2025-05-15 00:00:00')
        ->and($trends->projections->when->targetValue)
        ->toBe(1000)
        ->and($trends->projections->when->confidence)
        ->toBe(25);
})->with('databases');

it('can project what value a metric will have on a specific date with slow rate of change', function ($db) {
    createTestSchema($db);

    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    DB::table('test_data')->insert([
        [
            'name' => 'Item 15',
            'value' => 50,
            'category' => 'category1',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ],
        [
            'name' => 'Item 16',
            'value' => 10,
            'category' => 'category1',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ],
        [
            'name' => 'Item 17',
            'value' => 10,
            'category' => 'category1',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ],
        [
            'name' => 'Item 18',
            'value' => 10,
            'category' => 'category1',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ],
        [
            'name' => 'Item `19`',
            'value' => 5,
            'category' => 'category1',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ],
        [
            'name' => 'Item 20',
            'value' => 5,
            'category' => 'category1',
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ],
        [
            'name' => 'Item 21',
            'value' => 10,
            'category' => 'category1',
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Item 22',
            'value' => 5,
            'category' => 'category1',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $trends = $metrics->sum('value')
        ->between(now()->subDays(3), now())
        ->byDay()
        ->projectForDate(CarbonImmutable::parse('2025-06-28 17:08:34'))
        ->trends();

    expect($trends)
        ->toHaveKey('projections')
        ->and($trends->projections)
        ->toBeInstanceOf(Projections::class)
        ->and($trends->projections->date)
        ->toBeInstanceOf(DateProjection::class)
        ->and($trends->projections->date->projectedTotal)
        ->toBe(470.0)
        ->and($trends->projections->date->targetDate->toDateTimeString())
        ->toBe('2025-06-28 17:08:34')
        ->and($trends->projections->date->confidence)
        ->toBe(35);
})->with('databases');

it('does not project when with a single value', function ($db) {
    createTestSchema($db);

    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    DB::table('test_data')->insert([
        [
            'name' => 'Item 15',
            'value' => 100,
            'category' => 'category1',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ],
    ]);

    $trends = $metrics->sum('value')
        ->between(now()->subDays(3), now())
        ->byDay()
        ->projectWhen(200)
        ->trends();

    expect($trends)
        ->toHaveKey('projections')
        ->and($trends->projections)
        ->toBeInstanceOf(Projections::class)
        ->and($trends->projections->when)
        ->toBeInstanceOf(WhenProjection::class)
        ->and($trends->projections->when->projectedDate)
        ->toBeNull()
        ->and($trends->projections->when->targetValue)
        ->toBe(200)
        ->and($trends->projections->when->confidence)
        ->toBe(0);
})->with('databases');

it('does not project value with a single value', function ($db) {
    createTestSchema($db);

    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    DB::table('test_data')->insert([
        [
            'name' => 'Item 15',
            'value' => 100,
            'category' => 'category1',
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ],
    ]);

    $trends = $metrics->sum('value')
        ->between(now()->subDays(3), now())
        ->byDay()
        ->projectForDate(now()->addDays(5))
        ->trends();

    expect($trends)
        ->toHaveKey('projections')
        ->and($trends->projections)
        ->toBeInstanceOf(Projections::class)
        ->and($trends->projections->date)
        ->toBeInstanceOf(DateProjection::class)
        ->and($trends->projections->date->projectedTotal)
        ->toBeNull()
        ->and($trends->projections->date->targetDate->toDateTimeString())
        ->toBe('2025-04-15 02:38:15')
        ->and($trends->projections->date->confidence)
        ->toBe(0);
})->with('databases');

it('can project value to a future date from sparse dataset', function ($db) {
    createTestSchema($db);

    $data = collect([
        now()->subDays(3)->format('Y-m-d') => 6,
        now()->subDays(2)->format('Y-m-d') => 4,
        now()->subDays(1)->format('Y-m-d') => 11,
        now()->format('Y-m-d') => 4,
    ]);

    $data->each(function ($value, $date) {
        DB::table('test_data')->insert([
            [
                'name' => 'Item',
                'value' => $value,
                'category' => 'category1',
                'created_at' => CarbonImmutable::parse($date),
                'updated_at' => CarbonImmutable::parse($date),
            ],
        ]);
    });

    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder)
        ->sumByDay('value')
        ->all()
        ->projectForDate(CarbonImmutable::create(2025, 04, 22, 0, 0, 0))
        ->trends();

    expect($metrics->projections)
        ->toBeInstanceOf(Projections::class)
        ->and($metrics->projections->date)
        ->toBeInstanceOf(DateProjection::class)
        ->and($metrics->projections->date->targetDate->toDateTimeString())->toBe('2025-04-22 00:00:00')
        ->and($metrics->projections->date->projectedTotal)->toBe(87.0)
        ->and($metrics->projections->date->confidence)->toBe(22);
})->with('databases');

it('can project when a metric will reach a specific value from sparse dataset', function ($db) {
    createTestSchema($db);

    $data = collect([
        now()->subDays(3)->format('Y-m-d') => 6,
        now()->subDays(2)->format('Y-m-d') => 4,
        now()->subDays(1)->format('Y-m-d') => 11,
        now()->format('Y-m-d') => 4,
    ]);

    $data->each(function ($value, $date) {
        DB::table('test_data')->insert([
            [
                'name' => 'Item',
                'value' => $value,
                'category' => 'category1',
                'created_at' => CarbonImmutable::parse($date),
                'updated_at' => CarbonImmutable::parse($date),
            ],
        ]);
    });

    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder)
        ->sumByDay('value')
        ->all()
        ->projectWhen(100)
        ->trends();

    expect($metrics->projections)
        ->toBeInstanceOf(Projections::class)
        ->and($metrics->projections->when)
        ->toBeInstanceOf(WhenProjection::class)
        ->and($metrics->projections->when->targetValue)->toBe(100)
        ->and($metrics->projections->when->projectedDate->toDateTimeString())->toBe('2025-04-22 00:00:00')
        ->and($metrics->projections->when->confidence)->toBe(22);
})->with('databases');
