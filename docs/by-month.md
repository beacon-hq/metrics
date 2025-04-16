# By Month

The `->byMonth()` method is used to get per month trend data, it will start at the beginning of the
given month (`Y-m-1`) and end at the end of the given month (`Y-m-{28-31}`).

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->byMonth()
    ->...
```

You can also pass in a count argument to specify how many months you want to rollup data for. The default is 1 month.

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->byMonth(3) // Rollup every three months
    ->...
```

## Aliases

The `->byMonth()` method has the following aliases:

| Alias                                   | Equivalent                            |
|-----------------------------------------|---------------------------------------|
| `->countByMonth($column, $count = 1)`   | `->count($column)->byMonth($count)`   |
| `->sumByMonth($column, $count = 1)`     | `->sum($column)->byMonth($count)`     |
| `->averageByMonth($column, $count = 1)` | `->average($column)->byMonth($count)` |
| `->minByMonth($column, $count = 1)`     | `->min($column)->byMonth($count)`     |
| `->maxByMonth($column, $count = 1)`     | `->max($column)->byMonth($count)`     |
