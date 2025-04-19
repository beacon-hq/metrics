# All Data

You can retrieve metrics for all data in your database using the `->all()` method. This method will return the  metrics for all records.

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->all()
    ->...
```

> [!WARNING]
> You cannot use the `->all()` method and [`->withPrevious()`](value-metrics#previous-period-comparison) method together, 
> doing so will result in a `\Beacon\Exceptions\InvalidDateRangeException` exception.

> [!NOTE]
> The `->all()` method will issue a `SELECT` statement to determine the _actual_ oldest date in your dataset from which
> to start calculating metrics, rather than using an arbitrary historical date (e.g. `1970-01-01`). This allows it to create
> accurate interval metrics.
