<?php

declare(strict_types=1);

use Beacon\Metrics\HasMetrics;
use Illuminate\Database\Eloquent\Model;

covers(HasMetrics::class);

it('expands models', function ($db) {
    createTestData($db);

    $model = new class extends Model
    {
        use HasMetrics;

        protected $table = 'test_data';
    };

    $value = $model->metrics()->value();
    expect($value)->toBe(3);
})->with('databases');
