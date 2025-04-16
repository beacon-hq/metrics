# By Hour

The `->byHour()` method is used to get hourly trend data, it will start at the beginning of the
given hour (`XX:00:00`) and end at the end of the given hour (`XX:59:59`).

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->byHour()
    ->...
```

You can also pass in a count argument to specify how many hours you want to rollup data for. The default is 1 hour.

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->byHour(6) // Rollup every six hours
    ->...
```

## Aliases

The `->byHour()` method has the following aliases:

| Alias                                  | Equivalent                           |
|----------------------------------------|--------------------------------------|
| `->countByHour($column, $count = 1)`   | `->count($column)->byHour($count)`   |
| `->sumByHour($column, $count = 1)`     | `->sum($column)->byHour($count)`     |
| `->averageByHour($column, $count = 1)` | `->average($column)->byHour($count)` |
| `->minByHour($column, $count = 1)`     | `->min($column)->byHour($count)`     |
| `->maxByHour($column, $count = 1)`     | `->max($column)->byHour($count)`     |
