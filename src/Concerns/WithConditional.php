<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

trait WithConditional
{
    public function when(mixed $condition, callable $callback): self
    {
        if ($condition) {
            $callback($this);
        }

        return $this;
    }
}
