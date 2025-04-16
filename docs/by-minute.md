# By Minute

The `->byMinute()` method is used to get per minute trend data, it will start at the beginning of the
given minute (`XX:XX:00`) and end at the end of the given minute (`XX:XX:59`).

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->byMinute()
    ->...
```

You can also pass in a count argument to specify how many minutes you want to rollup data for. The default is 1 minute.

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->byMinute(30) // Rollup every thirty hours
    ->...
```

## Aliases

The `->byMinute()` method has the following aliases:

| Alias                                    | Equivalent                             |
|------------------------------------------|----------------------------------------|
| `->countByMinute($column, $count = 1)`   | `->count($column)->byMinute($count)`   |
| `->sumByMinute($column, $count = 1)`     | `->sum($column)->byMinute($count)`     |
| `->averageByMinute($column, $count = 1)` | `->average($column)->byMinute($count)` |
| `->minByMinute($column, $count = 1)`     | `->min($column)->byMinute($count)`     |
| `->maxByMinute($column, $count = 1)`     | `->max($column)->byMinute($count)`     |
