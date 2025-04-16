# By Second

The `->bySecond()` method is used to get per second trend data.

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->bySecond()
    ->...
```

You can also pass in a count argument to specify how many seconds you want to rollup data for. The default is 1 second.

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->bySecond(30) // Rollup every thirty seconds
    ->...
```

## Aliases

The `->bySecond()` method has the following aliases:

| Alias                                    | Equivalent                             |
|------------------------------------------|----------------------------------------|
| `->countBySecond($column, $count = 1)`   | `->count($column)->bySecond($count)`   |
| `->sumBySecond($column, $count = 1)`     | `->sum($column)->bySecond($count)`     |
| `->averageBySecond($column, $count = 1)` | `->average($column)->bySecond($count)` |
| `->minBySecond($column, $count = 1)`     | `->min($column)->bySecond($count)`     |
| `->maxBySecond($column, $count = 1)`     | `->max($column)->bySecond($count)`     |
