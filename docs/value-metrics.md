# Value Metrics

Value metrics are single values that represent an aggregate calculation across a time period and/or group.

They are typically used to provide a summary of data, such as the total number of items sold or the average price of items in a category.

To calculate a value metric, use the `->value()` method:

```php{6}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$value = Metrics::query(MyModel::query())
    ->...
    ->value();
```

This will return a single `int` or `float` value, depending on the metric being calculated.

## Grouping

You can group your metrics by a column using the `->groupBy()` method. This will group the results by the specified column.

```php{6}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->...
    ->groupBy('type')
    ->value();
```

This will return a Laravel `Collection` object with the group value as keys, and the aggregate value as the value. For example,
as an array it might look like the following:

```php
[
    'shirts' => 345,
    'pants' => 123,
    'shoes' => 456,
]
```

## Previous Period Comparison

To compare a metric against the previous period of time, you can use the `->withPrevious()` method:

```php{6}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->...
    ->withPrevious()
    ->value();
``` 

This will result in a Laravel `Collection` with the current value and the previous value information with the following structure:

```php
[
    'value' => 123,
    'previous' => [
        'value' => 456,
        'type' => 'increase', // or 'decrease'
        'difference' => 333,
        'percentage' => 75.0,
    ],
]
```

Notice that the `difference` and `percentage` are always positive values, and the `type` denotes whether it is an increase or decrease.
