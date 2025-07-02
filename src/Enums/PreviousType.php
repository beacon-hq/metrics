<?php

declare(strict_types=1);

namespace Beacon\Metrics\Enums;

enum PreviousType: string
{
    case INCREASE = 'increase';
    case DECREASE = 'decrease';
    case IDENTICAL = 'identical';
}
