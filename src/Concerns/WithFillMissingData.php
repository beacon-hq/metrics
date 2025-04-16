<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

trait WithFillMissingData
{
    protected bool $fillMissing = false;

    protected ?int $missingDataValue = 0;

    public function fillMissing(?int $missingDataValue = 0): self
    {
        $this->fillMissing = true;
        $this->missingDataValue = $missingDataValue;

        return $this;
    }
}
