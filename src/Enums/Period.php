<?php

declare(strict_types=1);

namespace Beacon\Metrics\Enums;

enum Period: string
{
    case LAST_10_MINUTES = 'last_10_minutes';
    case LAST_12_HOURS = 'last_12_hours';
    case LAST_15_MINUTES = 'last_15_minutes';
    case LAST_24_HOURS = 'last_24_hours';
    case LAST_2_HOURS = 'last_2_hours';
    case LAST_30_DAYS = 'last_30_days';
    case LAST_30_DAYS_INCLUDING_TODAY = 'last_30_days_with_today';
    case LAST_30_MINUTES = 'last_30_minutes';
    case LAST_365_DAYS = 'last_365_days';
    case LAST_365_DAYS_INCLUDING_TODAY = 'last_365_days_with_today';
    case LAST_5_MINUTES = 'last_5_minutes';
    case LAST_60_DAYS = 'last_60_days';
    case LAST_60_DAYS_INCLUDING_TODAY = 'last_60_days_with_today';
    case LAST_6_HOURS = 'last_6_hours';
    case LAST_7_DAYS = 'last_7_days';
    case LAST_7_DAYS_INCLUDING_TODAY = 'last_7_days_with_today';
    case LAST_90_DAYS = 'last_90_days';
    case LAST_90_DAYS_INCLUDING_TODAY = 'last_90_days_with_today';
    case LAST_HOUR = 'last_hour';
    case LAST_MINUTE = 'last_minute';
    case LAST_THIRTY_SECONDS = 'last_30_seconds';
    case MONTH_TO_DATE = 'this_month';
    case PREVIOUS_HOUR = 'last_hour_previous';
    case PREVIOUS_MINUTE = 'last_minute_previous';
    case PREVIOUS_MONTH = 'last_month';
    case PREVIOUS_YEAR = 'last_year';
    case TODAY = 'today';
    case YEAR_TO_DATE = 'this_year';
    case YESTERDAY = 'yesterday';
}
