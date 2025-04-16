# By Year

The `->byYear()` method is used to get per year trend data, it will start at the beginning of the
given year (`Y-01-01 00:00:00`) and end at the end of the given year (`Y-12-31 00:00:00`).

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->byYear()
    ->...
```

You can also pass in a count argument to specify how many years you want to rollup data for. The default is 1 year.

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->byYear(2) // Rollup every two years
    ->...
```

## Aliases

The `->byYear()` method has the following aliases:

| Alias                                  | Equivalent                           |
|----------------------------------------|--------------------------------------|
| `->countByYear($column, $count = 1)`   | `->count($column)->byYear($count)`   |
| `->sumByYear($column, $count = 1)`     | `->sum($column)->byYear($count)`     |
| `->averageByYear($column, $count = 1)` | `->average($column)->byYear($count)` |
| `->minByYear($column, $count = 1)`     | `->min($column)->byYear($count)`     |
| `->maxByYear($column, $count = 1)`     | `->max($column)->byYear($count)`     |
