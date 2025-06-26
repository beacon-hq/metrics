# Conditional Statements

When constructing your metrics, you may want to apply chained methods only when certain conditions are met. 
For example, you might want to change the granularity of your metrics based on a request parameter, 
such as an `interval` that could be `day` or `month`.

You can achieve this using the `->when()` method, which allows you to conditionally apply a method based on a boolean expression:

```php
$interval = request()->get('interval');

Metrics::query(MyModel::query())
    ->count()
    ->when($interval === 'day', fn (Metrics $metrics) => $metrics->byDay())
    ->when($interval === 'month', fn (Metrics $metrics) => $metrics->byMonth())
    ->between(now()->subYear(), now())
    ->trends();
```
