<?php

declare(strict_types=1);

namespace Beacon\Metrics\Values;

use Bag\Bag;
use Bag\Values\Optional;
use Beacon\Metrics\Values\Projections\DateProjection;
use Beacon\Metrics\Values\Projections\WhenProjection;

readonly class Projections extends Bag
{
    public function __construct(
        public Optional|WhenProjection $when,
        public Optional|DateProjection $date,
    ) {}
}
