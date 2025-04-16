# By Day Name

The `->byDayName()` method is used to get daily trend data by day of the week, it will start at the beginning of the
given day (`00:00:00`) and end at the end of the given day (`23:59:59`).

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->byDayName()
    ->...
```

You can also pass in a count argument to specify how many days you want to rollup data for. The default is 1 day.

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->byDayName(3) // Rollup every third day
    ->...
```

## Aliases

The `->byDayName()` method has the following aliases:

| Alias                                     | Equivalent                              |
|-------------------------------------------|-----------------------------------------|
| `->countByDayName($column, $count = 1)`   | `->count($column)->byDayName($count)`   |
| `->sumByDayName($column, $count = 1)`     | `->sum($column)->byDayName($count)`     |
| `->averageByDayName($column, $count = 1)` | `->average($column)->byDayName($count)` |
| `->minByDayName($column, $count = 1)`     | `->min($column)->byDayName($count)`     |
| `->maxByDayName($column, $count = 1)`     | `->max($column)->byDayName($count)`     |
