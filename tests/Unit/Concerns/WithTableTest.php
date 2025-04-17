<?php

declare(strict_types=1);

use Beacon\Metrics\Concerns\WithTable;
use Beacon\Metrics\Metrics;
use Illuminate\Support\Facades\DB;

covers(WithTable::class);

test('it sets table correctly', function ($db) {
    createTestData($db);

    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder)->table('custom_table');

    expect($this->prop($metrics, 'table'))->toBe('custom_table');
})->with('databases');
