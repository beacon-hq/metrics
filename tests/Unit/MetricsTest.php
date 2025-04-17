<?php

declare(strict_types=1);

use Beacon\Metrics\Metrics;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

covers(Metrics::class);

test('it initializes correctly with query', function ($db) {
    createTestData($db);
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    expect($metrics)->toBeInstanceOf(Metrics::class);

    $model = new class extends Model {};
    $metrics = Metrics::query($model->query());
    expect($metrics)->toBeInstanceOf(Metrics::class);
})->with('databases');
