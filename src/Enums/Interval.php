<?php

declare(strict_types=1);

namespace Beacon\Metrics\Enums;

enum Interval: string
{
    case SECOND = 'second';
    case MINUTE = 'minute';
    case HOUR = 'hour';
    case DAY = 'day';
    case DAY_OF_WEEK = 'day_of_week';
    case MONTH = 'month';
    case WEEK = 'week';
    case YEAR = 'year';
}
