<?php

declare(strict_types=1);

namespace Beacon\Metrics\Values;

use Bag\Bag;
use Beacon\Metrics\Enums\PreviousType;

readonly class PreviousValue extends Bag
{
    public function __construct(
        public PreviousType $type,
        public int|float $value,
        public int|float $difference,
        public int|float $percentage,
    ) {}
}
