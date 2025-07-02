<?php

declare(strict_types=1);

namespace Beacon\Metrics\Values\Projections;

use Bag\Bag;
use Carbon\CarbonImmutable;

readonly class WhenProjection extends Bag
{
    public function __construct(
        public int|float $targetValue,
        public ?CarbonImmutable $projectedDate,
        public int $confidence,
    ) {}
}
