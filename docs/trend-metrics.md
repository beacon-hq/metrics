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

This will result in a `TrendMetric` object that looks like this:

```php
TrendMetric<{
    'labels' => Collection<string>, // the [sorted] date labels for the data
    'data' => Collection<float|int>, // the aggregate values for the labels
    'total' => float|int, // the total for the entire result set
    'projections' => Optional|ProjectionValue, // optional projections for the trend
}>
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
> Counterintuitively, it can be faster to include missing intervals as the database will always calculate them and will
> have to omit them explicitly when calculating the result set.

## Grouping

You can group your metrics by a column using the `->groupBy()` method. This will group the results by the specified column.

```php{6}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->...
    ->groupBy('type')
    ->trends();
```

This will return a `TrendMetricCollection` object with the group value as keys, and the `TrendMetric` as the values:

```php
TrendMetricCollection<mixed, TrendMetric>
```

For example, as an array a result might look like the following:



```php
[
    'shirts' => [
        'labels' => [
            '2025-04-07', 
            '2025-04-08', 
            '2025-04-09', 
            '2025-04-10', 
            '2025-04-11',
        ],
        'data' => [100, 200, 300, 0, 400],
        'total' => 1000,
    ],
    'pants' => [
        'labels' => [
            '2025-04-07', 
            '2025-04-08', 
            '2025-04-09', 
            '2025-04-10', 
            '2025-04-11',
        ],
        'data' => [50, 100, 150, 0, 200],
        'total' => 500,
    ],
    'shoes' => [
        'labels' => [
            '2025-04-07', 
            '2025-04-08', 
            '2025-04-09', 
            '2025-04-10', 
            '2025-04-11',
        ],
        'data' => [25, 50, 75, 0, 100],
        'total' => 250,
    ],
]
```

## Associative Result Sets

You can return label-value pairs of separate arrays using the `->assoc()` method:

```php{4}
$metrics = Metrics::query(MyModel::query())
    ->...
    ->trends()
    ->assoc();
```

Will return a Laravel `Collection` that looks like this:

```php
[
    '2025-04-07' => 100, 
    '2025-04-08' => 200,
    '2025-04-09' => 300, 
    '2025-04-10' => 0, 
    '2025-04-11' => 400,
]
```

If you also want a `total` value, you can pass `true` to the `->assoc()` method.

```php{4}

> [!TIP]
> `->assoc()` also works with `TrendMetricCollection` objects, allowing you to get associative arrays for each group.

## Projections

Projections allow you to predict future values based on the trend of your data. There are two types of projections available:

1. **Value Projection**: Predict when a metric will reach a specific value
2. **Date Projection**: Predict what a metric's value will be at a specific date

Projections are returned as `ProjectionValue` objects as part of the `TrendMetric` object, `TrendMetric->projections`:

```php
ProjectionValue<{
    'when' => Optional|ProjectionWhen<{
        targetValue: int|float|null,
        projectedDate: \Carbon\CarbonImmutable, // the date when the value is projected to be reached
        confidence: int, // confidence level (0-100) based on data consistency
    }>
    'date' => Optional|ProjectionDate<{
        targetDate: ?\Carbon\CarbonImmutable, // the target date for the projection
        projectedTotal: int|float, // the projected value at the target date
        confidence: int, // confidence level (0-100) based on data consistency
    }>
}>
```

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

This will result in `->projections` being a `ProjectionValue` object to the result with a `when` property containing:

```php
[
    'projections' => [
        'when' => [
            'targetValue' => 1000,
            'projectedDate' => '2025-05-15 00:00:00', // The date when the value is projected to reach 1000
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

This will set the `projections` value for the result with a `date` subkey containing:

```php
[
    'projections' => [
        'date' => [
            'target_date' => '2025-05-10 00:00:00', // The target date
            'projected_total' => 850.5, // The projected value at the target date
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

This will return a `TrendMetricCollection` where each group has its own projections:

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
