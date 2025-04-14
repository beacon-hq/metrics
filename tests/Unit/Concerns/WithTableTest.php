<?php

declare(strict_types=1);

use Beacon\Metrics\Concerns\WithTable;
use Beacon\Metrics\Metrics;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

covers(WithTable::class);

beforeEach(function () {
    file_exists(database_path('table_test.sqlite')) && unlink(database_path('table_test.sqlite'));
    touch(database_path('table_test.sqlite'));
    $this->db = new DB;
    $this->db->addConnection([
        'driver' => 'sqlite',
        'database' => database_path('table_test.sqlite'),
    ]);
    $this->db->setAsGlobal();
    $this->db->bootEloquent();

    // Create test_data table
    $this->db->schema()->create('test_data', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->integer('value');
        $table->string('category');
        $table->timestamps();
    });
});

test('it sets table correctly', function () {
    $builder = DB::table('test_data');
    $metrics = Metrics::query($builder)->table('custom_table');

    expect($this->prop($metrics, 'table'))->toBe('custom_table');
});
