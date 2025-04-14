<?php

declare(strict_types=1);

namespace Beacon\Metrics\Enums;

enum Aggregate: string
{
    case AVERAGE = 'avg';
    case COUNT = 'count';
    case MAX = 'max';
    case MIN = 'min';
    case SUM = 'sum';
}
