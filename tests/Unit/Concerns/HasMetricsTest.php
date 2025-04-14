<?php

declare(strict_types=1);

use Beacon\Metrics\HasMetrics;
use Illuminate\Database\Eloquent\Model;

it('expands models', function () {
    $model = new class extends Model
    {
        use HasMetrics;

        protected $table = 'test_data';
    };

    $value = $model->metrics()->value();
    expect($value)->toBe(3);
});
