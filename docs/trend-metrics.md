# Trend Metrics

Trends metrics are used to calculate the value of a metric over a period of time. This is useful for visualizing how a metric changes over time, such as daily, weekly, or monthly.

They are intended to be used to generate graphs or reports.

To calculate trend metrics, use the `->trends()` method:

```php
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->...
    ->trends();
```

This will result in a Laravel `Collection` with `labels`, `data`, and `total` keys. As an array it might look like the following:

```php{2,8,14}
[
    'labels' => [
        '2025-04-07', 
        '2025-04-08', 
        '2025-04-09', 
        '2025-04-11', 
    ],
    'data' => [
        100,
        200,
        300,
        400,
    ],
    'total' => 1000,
]
```

## Fill Missing Values

By default, intervals for missing values are omitted, this means that your data may be non-contiguous.

To include missing intervals, you can use the `->fillMissing()` method:

```php
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->...
    ->fillMissing()
    ->trends();
```

Now the above result set might look like:

```php{6,13}
[
    'labels' => [
        '2025-04-07', 
        '2025-04-08', 
        '2025-04-09', 
        '2025-04-10', // added
        '2025-04-11', 
    ],
    'data' => [
        100,
        200,
        300,
        0, // added
        400,
    ],
    'total' => 1000,
]
```

> [!NOTE]
> Counter-intuitively, it can be faster to include missing intervals as the database will always calculate them and will
> have to omit them explicitly when calculating the result set.
