<?php

declare(strict_types=1);

use Beacon\Metrics\Concerns\WithProjection;
use Beacon\Metrics\Metrics;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
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
        ->projectWhen(200)
        ->trends();

    expect($trends)
        ->toHaveKey('projections')
        ->and($trends['projections'])
        ->toHaveKey('when')
        ->and($trends['projections']['when'])
        ->toHaveKey('target_value')
        ->and($trends['projections']['when'])
        ->toHaveKey('projected_date')
        ->and($trends['projections']['when'])
        ->toHaveKey('confidence')
        ->and($trends['projections']['when']['target_value'])
        ->toBe(200);

    $expectedDate = now()->addDays(7)->startOfDay();
    $projectedDate = CarbonImmutable::parse($trends['projections']['when']['projected_date'])->startOfDay();

    expect($projectedDate->diffInDays($expectedDate))
        ->toBeLessThanOrEqual(1);
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

    expect($trends)->toHaveKey('projections')
        ->and($trends['projections'])->toHaveKey('date')
        ->and($trends['projections']['date'])->toHaveKey('target_date')
        ->and($trends['projections']['date'])->toHaveKey('projected_value')
        ->and($trends['projections']['date'])->toHaveKey('confidence')
        ->and($trends['projections']['date']['target_date'])->toBe($targetDate->toDateTimeString())
        ->and($trends['projections']['date']['projected_value'])->toBe(510.0);

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
        ->projectWhen(200)
        ->projectForDate($targetDate)
        ->trends();

    expect($trends)->toHaveKey('projections')
        ->and($trends['projections'])->toHaveKey('when')
        ->and($trends['projections'])->toHaveKey('date')
        ->and($trends['projections']['when']['target_value'])->toBe(200)
        ->and($trends['projections']['date']['target_date'])->toBe($targetDate->toDateTimeString());

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
        ->projectWhen(200)
        ->projectForDate($targetDate)
        ->trends();

    expect($trends)->toBeInstanceOf(Collection::class)
        ->and($trends->has('category1'))->toBeTrue()
        ->and($trends['category1'])->toHaveKey('projections')
        ->and($trends['category1']['projections'])->toHaveKey('when')
        ->and($trends['category1']['projections'])->toHaveKey('date')
        ->and($trends['category1']['projections']['when']['target_value'])->toBe(200)
        ->and($trends['category1']['projections']['date']['projected_value'])->toBe(510.0)
        ->and($trends->has('category2'))->toBeTrue()
        ->and($trends['category2'])->toHaveKey('projections')
        ->and($trends['category2']['projections']['when']['projected_date'])->toBeNull()
        ->and($trends['category2']['projections']['date']['projected_value'])->toBe(1020.0);
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
        ->projectWhen(500)
        ->trends();

    expect($trends)
        ->toHaveKey('projections')
        ->and($trends['projections'])
        ->toHaveKey('when')
        ->and($trends['projections']['when'])
        ->toHaveKey('target_value')
        ->and($trends['projections']['when'])
        ->toHaveKey('projected_date')
        ->and($trends['projections']['when']['projected_date'])
        ->toBe('2025-06-28 17:08:34')
        ->and($trends['projections']['when'])
        ->toHaveKey('confidence')
        ->and($trends['projections']['when']['target_value'])
        ->toBe(500);

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
        ->and($trends['projections'])
        ->toHaveKey('date')
        ->and($trends['projections']['date'])
        ->toHaveKey('target_date')
        ->and($trends['projections']['date'])
        ->toHaveKey('projected_value')
        ->and($trends['projections']['date']['projected_value'])
        ->toBe(105.0)
        ->and($trends['projections']['date'])
        ->toHaveKey('confidence')
        ->and($trends['projections']['date']['target_date'])
        ->toBe('2025-06-28 17:08:34');

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
        ->and($trends['projections'])
        ->toHaveKey('when')
        ->and($trends['projections']['when'])
        ->toHaveKey('target_value')
        ->and($trends['projections']['when'])
        ->toHaveKey('projected_date')
        ->and($trends['projections']['when']['projected_date'])
        ->toBeNull()
        ->and($trends['projections']['when'])
        ->toHaveKey('confidence')
        ->and($trends['projections']['when']['target_value'])
        ->toBe(200);
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
        ->and($trends['projections'])
        ->toHaveKey('date')
        ->and($trends['projections']['date'])
        ->toHaveKey('target_date')
        ->and($trends['projections']['date'])
        ->toHaveKey('projected_value')
        ->and($trends['projections']['date']['projected_value'])
        ->toBeNull()
        ->and($trends['projections']['date'])
        ->toHaveKey('confidence')
        ->and($trends['projections']['date']['target_date'])
        ->toBe('2025-04-15 02:38:15');
})->with('databases');
