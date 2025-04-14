<?php

declare(strict_types=1);

use Beacon\Metrics\Metrics;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Model;

covers(Metrics::class);

beforeEach(function () {
    $this->db = new DB;
    $this->db->addConnection([
        'driver' => 'sqlite',
        'database' => ':memory:',
    ]);
    $this->db->setAsGlobal();
    $this->db->bootEloquent();
});

test('it initializes correctly with query', function () {
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder);

    expect($metrics)->toBeInstanceOf(Metrics::class);

    $model = new class extends Model {};
    $metrics = Metrics::query($model->query());
    expect($metrics)->toBeInstanceOf(Metrics::class);
});
