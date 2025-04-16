# By Week

The `->byWeek()` method is used to get per week trend data, it will start at the beginning of the
given week (`Y-m-d`) and end at the end of the given week (`Y-m-d`).

Weeks are returned using `Y-\WW` format, with 1-based weeks, with leading zeros, e.g. `2025-W01` is the first week of the year.

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->byWeek()
    ->...
```

You can also pass in a count argument to specify how many weeks you want to rollup data for. The default is 1 week.

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->byWeek(2) // Rollup every two weeks
    ->...
```

## Aliases

The `->byWeek()` method has the following aliases:

| Alias                                  | Equivalent                           |
|----------------------------------------|--------------------------------------|
| `->countByWeek($column, $count = 1)`   | `->count($column)->byWeek($count)`   |
| `->sumByWeek($column, $count = 1)`     | `->sum($column)->byWeek($count)`     |
| `->averageByWeek($column, $count = 1)` | `->average($column)->byWeek($count)` |
| `->minByWeek($column, $count = 1)`     | `->min($column)->byWeek($count)`     |
| `->maxByWeek($column, $count = 1)`     | `->max($column)->byWeek($count)`     |
