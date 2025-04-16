# For Period

To calculate metrics for a specific period (e.g. the result of date math), you can use the `->period($period)` method.

The period value should either be a `\Beacon\Metrics\Enums\Period` enum value, or a `DatePeriod`-like instance (e.g. `\Carbon\CarbonPeriod`).

```php{5,8}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;
use Carbon\CarbonPeriod;

$period = CarbonPeriod::between('2025-01-01', '2025-01-31');

$metrics = Metrics::query(MyModel::query())
    ->period($period)
    ->...
```

Possible `\Beacon\Metrics\Enums\Period` values are:

- `Period::LAST_10_MINUTES`
- `Period::LAST_12_HOURS`
- `Period::LAST_15_MINUTES`
- `Period::LAST_24_HOURS`
- `Period::LAST_2_HOURS`
- `Period::LAST_30_DAYS`
- `Period::LAST_30_DAYS_INCLUDING_TODAY`
- `Period::LAST_30_MINUTES`
- `Period::LAST_365_DAYS`
- `Period::LAST_365_DAYS_INCLUDING_TODAY`
- `Period::LAST_5_MINUTES`
- `Period::LAST_60_DAYS`
- `Period::LAST_60_DAYS_INCLUDING_TODAY`
- `Period::LAST_6_HOURS`
- `Period::LAST_7_DAYS`
- `Period::LAST_7_DAYS_INCLUDING_TODAY`
- `Period::LAST_90_DAYS`
- `Period::LAST_90_DAYS_INCLUDING_TODAY`
- `Period::LAST_HOUR`
- `Period::LAST_MINUTE`
- `Period::LAST_THIRTY_SECONDS`
- `Period::MONTH_TO_DATE`
- `Period::PREVIOUS_HOUR`
- `Period::PREVIOUS_MINUTE`
- `Period::PREVIOUS_MONTH`
- `Period::PREVIOUS_YEAR`
- `Period::TODAY`
- `Period::YEAR_TO_DATE`
- `Period::YESTERDAY`
