<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

trait WithValue
{
    protected function formatNumber(null|int|float $number, int $decimalPlaces = -1): int|float
    {
        if ($number === null) {
            return $this->missingDataValue;
        }

        if (fmod($number, 1.00) === 0.0) {
            return (int) $number;
        }

        if ($decimalPlaces === -1) {
            return (float) $number;
        }

        return round($number, $decimalPlaces);
    }
}
