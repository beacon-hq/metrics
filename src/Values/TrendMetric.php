<?php

declare(strict_types=1);

namespace Beacon\Metrics\Values;

use Bag\Attributes\Collection;
use Bag\Bag;
use Bag\Values\Optional;
use Beacon\Metrics\Values\Collections\TrendMetricCollection;
use Illuminate\Support\Collection as LaravelCollection;

#[Collection(TrendMetricCollection::class)]
readonly class TrendMetric extends Bag
{
    public function __construct(
        public LaravelCollection $labels,
        public LaravelCollection $data,
        public int|float $total,
        public Optional|Projections $projections,
    ) {}

    public function assoc(bool $withTotal = false): LaravelCollection
    {
        return $this->labels
            ->combine($this->data)
            ->when($withTotal, fn (LaravelCollection $collection) => $collection->put('total', $this->total));
    }
}
