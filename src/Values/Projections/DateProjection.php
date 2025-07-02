<?php

declare(strict_types=1);

namespace Beacon\Metrics\Values\Projections;

use Bag\Bag;
use Carbon\CarbonImmutable;

readonly class DateProjection extends Bag
{
    public function __construct(
        public CarbonImmutable $targetDate,
        public int|float|null $projectedTotal,
        public int $confidence,
    ) {}
}
