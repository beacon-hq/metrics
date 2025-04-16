# By Day

The `->byDay()` method is used to get daily trend data, it will start at the beginning of the
given day (`00:00:00`) and end at the end of the given day (`23:59:59`).

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->byDay()
    ->...
```

You can also pass in a count argument to specify how many days you want to rollup data for. The default is 1 day.

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->byDay(3) // Rollup to every third day
    ->...
```

## Aliases

The `->byDay()` method has the following aliases:

| Alias                               | Equivalent                          |
|-------------------------------------|-------------------------------------|
| `->countByDay($column, $count = 1)`   | `->count($column)->byDay($count)`   |
| `->sumByDay($column, $count = 1)`     | `->sum($column)->byDay($count)`     |
| `->averageByDay($column, $count = 1)` | `->average($column)->byDay($count)` |
| `->minByDay($column, $count = 1)`     | `->min($column)->byDay($count)`     |
| `->maxByDay($column, $count = 1)`     | `->max($column)->byDay($count)`     |
