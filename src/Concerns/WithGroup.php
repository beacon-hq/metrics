<?php

declare(strict_types=1);

namespace Beacon\Metrics\Concerns;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

trait WithGroup
{
    protected ?Expression $groupBy = null;

    public function groupBy(string|Expression $groupBy): self
    {
        if ($groupBy instanceof Expression) {
            $groupBy = $groupBy->getValue($this->builder->getGrammar());
        }

        $groupBy = Str::replace(' AS ', ' as ', $groupBy);
        $this->groupBy = DB::raw(Str::before($groupBy, ' as ').' as grp');

        return $this;
    }
}
