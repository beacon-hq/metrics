<?php

declare(strict_types=1);

namespace Beacon\Metrics\Values;

use Bag\Attributes\Collection;
use Bag\Bag;
use Bag\Values\Optional;
use Beacon\Metrics\Values\Collections\ValueMetricCollection;

#[Collection(ValueMetricCollection::class)]
readonly class ValueMetric extends Bag
{
    public function __construct(
        public int|float $value,
        public Optional|PreviousValue $previous,
    ) {}

    public function toFloat(): float
    {
        return (float) $this->value;
    }

    public function toInt(): int
    {
        return (int) $this->value;
    }

    public function toNumber(): int|float
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
