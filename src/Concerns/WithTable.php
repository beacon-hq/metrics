<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

trait WithTable
{
    protected string $table;

    public function table(string $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function bootWithTable(): void
    {
        $this->table = $this->builder->from;
    }
}
