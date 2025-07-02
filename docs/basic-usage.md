# Basic Usage

Beacon Metrics uses a fluent api to compose your metrics.

## Metrics Composition

A metrics query is composed of the following parts:

- A base query instance (e.g. a model or DB query)
- An aggregate function (i.e. count, sum, average, min, max)
- A value column (e.g. the column you want to aggregate)
- An interval (e.g. by day, by week, by month)
- An interval count (e.g. every 2 days, every 3 weeks)
- A date range (e.g. between two dates, from a date)
- A date column (e.g. created_at, updated_at)
- [Optional] A grouping column (e.g. a column to group by)

A calculate method is then called to execute the query and return the results.

## Base Query

Everything starts the `Metrics::query()` method. This method accepts a model query or 
DB query instance as the base query.

```php
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query());
// or
$metrics = Metrics::query(DB::table('my_table'));
```

## Aggregate Functions

Beacon Metrics supports the following aggregate functions:

- `count`
- `sum`
- `average`
- `min`
- `max`

You can use the `->count()`, `->sum()`, `->average()`, `->min()`, or `->max()` methods to specify the aggregate function you want to use,
passing in the column name you want to aggregate.

```php
use Beacon\Metrics\Metrics;

$metrics->count('value');
```

## Intervals

You can specify the interval for your metrics using the `->by*()` methods. The following intervals are supported:

- `bySecond($count)`
- `byMinute($count)`
- `byHour($count)`
- `byDay($count)`
- `byDayOfWeek($count)`
- `byWeek($count)`
- `byMonth($count)`
- `byYear($count)`

You can use these methods to specify the interval you want to use. For example, to get the metrics by day:

```php
$metrics->byDay();
```

## Date Ranges & Column

All metrics must be calculated over a date range. You can use the `->between()`, `->from()`, and `for*()` methods to specify the date range.

For example, to get the metrics for the previous month:

```php
$metrics->between(now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth());
```

or to get the metrics for the last 7 days, including today:

```php
$metrics->from(now()->subDays(6));
```

To specify the date column, you can use the `->dateColumn()` method. Beacon Metrics will use the `created_at` column by default.

```php
$metrics->dateColumn('updated_at');
```

## Grouping

You can group your metrics by a column using the `->groupBy()` method. This will group the results by the specified column.

```php
$metrics->groupBy('category');
```


## Calculating Metrics

There are two types of metrics: value metrics and trend metrics.

### Value Metrics

Value metrics are simple single aggregate values and will typically result in a single value (unless using `Metrics->groupBy()`).

```php
$value = $metrics->value();
```

The `->value()` method will either return a `ValueMetric` object for the specified date range, with a `->value` property containing a single `float` or `int` value.

However, when using `->groupBy()`, the `->value()` method will return a  `ValueMetricCollection` with the group value as keys, and the aggregate `ValueMetric` as the value:

```php
ValueMetricCollection<mixed, ValueMetric> 
```

#### Calculating Previous Period Comparison

A common pattern when calculating simple metrics is to get the metrics for the current period (e.g. today, this week) and the previous period (e.g. yesterday, last week),
this can be achieved using the `Metric->withPrevious()` method.

```php
$previous = $metrics->withPrevious()->value();
```

This will return a `ValueMetric` object with the following structure:

```php
ValueMetric<{
    'value' => float|int, // the value for the current period
    'previous' => [
        'value' => float|int, // the value for the previous period
        // If there is a difference the following will also be set:
        'type' => PreviousType::class, // increase, decrease, or identical
        'difference' => float|int, // the difference between the two values
        'percentage' => float|int, // the percentage difference between the two values
    ],
}>
```

for example, as an array it would look like:

```php
[
  'value' => 100,
  'previous' => [
      'type' => 'increase',
      'value' => 80,
      'difference' => 20,
      'percentage' => 20,
  ],
]
```

### Trends

Trends metrics allow you to get metrics for intervals of a time period, e.g. every day, every month, or multiples of an interval,
e.g. every third day, every second month.

```php
$metrics->trends();
```

The `->trends()` method will return a `TrendMetric` with the following structure:

```php
TrendMetric<{
    'labels' => Collection<string>, // the [sorted] date labels for the data
    'data' => Collection<float|int>, // the aggregate values for the labels
    'total' => float|int, // the total for the entire result set
}>
```

As an array it might look like this:

```php
[
 'labels' => [
     '2025-04-07', 
     '2025-04-08', 
     '2025-04-10'
 ], 
 'data' => [
     3, 
     6, 
     9
 ], 
 'total' => 18
]
```

> [!TIP]
> By default, the `->trends()` result will **not** have results for intervals that have no data.

#### Filling Missing Data

You can ensure that all intervals are returned, even if there is no data for that interval, by using the `->fillMissing()` method.

```php
// defaults to filling with zeroes
$metrics->fillMissing()->trends();
// or specify a value, e.g. null
$metrics->fillMissing(null)->trends();
```

The resulting structure will be the same, however it will not have missing intervals, and all values will be filled with the specified value.

```php
[
 'labels' => [
     '2025-04-07', 
     '2025-04-08', 
     '2025-04-09', // added
     '2025-04-10'
 ], 
 'data' => [
     3, 
     6, 
     0, // added
     9
 ], 
 'total' => 18
]
```
