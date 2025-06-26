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

## Projections

Projections allow you to predict future values based on the trend of your data. There are two types of projections available:

1. **Value Projection**: Predict when a metric will reach a specific value
2. **Date Projection**: Predict what a metric's value will be at a specific date

### Value Projection

To project when a metric will reach a specific value, use the `->projectWhen()` method:

```php
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->sum('value')
    ->between(now()->subDays(30), now())
    ->byDay()
    ->projectWhen(1000) // When will the sum reach 1000?
    ->trends();
```

This will add a `projections` key to the result with a `when` subkey containing:

```php
[
    'projections' => [
        'when' => [
            'target_value' => 1000,
            'projected_date' => '2025-05-15 00:00:00', // The date when the value is projected to reach 1000
            'confidence' => 85, // Confidence level (0-100) based on data consistency
        ],
    ],
    // ... other trend data
]
```

### Date Projection

To project what a metric's value will be at a specific date, use the `->projectForDate()` method:

```php
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$targetDate = now()->addMonths(1);
$metrics = Metrics::query(MyModel::query())
    ->sum('value')
    ->between(now()->subDays(30), now())
    ->byDay()
    ->projectForDate($targetDate) // What will the sum be in one month?
    ->trends();
```

This will add a `projections` key to the result with a `date` subkey containing:

```php
[
    'projections' => [
        'date' => [
            'target_date' => '2025-05-10 00:00:00', // The target date
            'projected_value' => 850.5, // The projected value at the target date
            'confidence' => 78, // Confidence level (0-100) based on data consistency
        ],
    ],
    // ... other trend data
]
```

### Chaining Projections

You can chain multiple projection methods to get both types of projections in a single query:

```php
$metrics = Metrics::query(MyModel::query())
    ->sum('value')
    ->between(now()->subDays(30), now())
    ->byDay()
    ->projectWhen(1000) // When will the sum reach 1000?
    ->projectForDate(now()->addMonths(1)) // What will the sum be in one month?
    ->trends();
```

### Grouped Projections

Projections also work with grouped data. When using `->groupBy()`, each group will have its own projections:

```php
$metrics = Metrics::query(MyModel::query())
    ->sum('value')
    ->between(now()->subDays(30), now())
    ->byDay()
    ->groupBy('category')
    ->projectWhen(1000)
    ->projectForDate(now()->addMonths(1))
    ->trends();
```

This will return a collection where each group has its own projections:

```php
[
    'category1' => [
        'projections' => [
            'when' => [...],
            'date' => [...],
        ],
        // ... other trend data for category1
    ],
    'category2' => [
        'projections' => [
            'when' => [...],
            'date' => [...],
        ],
        // ... other trend data for category2
    ],
]
```

> [!NOTE]
> Projections are based on the trend of your data and assume that the trend will continue. The confidence level indicates how reliable the projection is based on the consistency of your data and the number of data points available.
