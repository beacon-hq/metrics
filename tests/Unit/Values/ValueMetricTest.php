<?php

declare(strict_types=1);

use Beacon\Metrics\Values\ValueMetric;

it('returns an int', function () {
    expect(ValueMetric::from(value: 12.3)->toInt())
        ->toBeInt()
        ->toBe(12)
        ->and(ValueMetric::from(value: 12)->toInt())
        ->toBeInt()
        ->toBe(12);
});

it('returns an float', function () {
    expect(ValueMetric::from(value: 12)->toFloat())
        ->toBeFloat()
        ->toBe(12.0)
        ->and(ValueMetric::from(value: 12.3)->toFloat())
        ->toBeFloat()
        ->toBe(12.3);
});

it('returns original number', function () {
    expect(ValueMetric::from(value: 12)->toNumber())
        ->toBeInt()
        ->toBe(12)
        ->and(ValueMetric::from(value: 12.3)->toNumber())
        ->toBeFloat()
        ->toBe(12.3);
});

it('casts to a string', function () {
    expect((string) ValueMetric::from(value: 12))
        ->toBeString()
        ->toBe('12')
        ->and((string) ValueMetric::from(value: 12.3))
        ->toBeString()
        ->toBe('12.3');
});
