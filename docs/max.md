# Max Aggregate

The max aggregate is used to get the maximum value of the values for a specific column.

## Usage

The `->max($column)` method is used to specify the max aggregate.

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->max('column_name')
    ->...
```

## Aliases

The `->max()` method has the following aliases:

| Alias                       | Equivalent                      |
|-----------------------------|---------------------------------|
| `->maxBySecond($column)`    | `->max($column)->bySecond()`    |
| `->maxByMinute($column)`    | `->max($column)->byMinute()`    |
| `->maxByHour($column)`      | `->max($column)->byHour()`      |
| `->maxByDay($column)`       | `->max($column)->byDay()`       |
| `->maxByDayOfWeek($column)` | `->max($column)->byDayOfWeek()` |
| `->maxByWeek($column)`      | `->max($column)->byWeek()`      |
| `->maxByMonth($column)`     | `->max($column)->byMonth()`     |
| `->maxByYear($column)`      | `->max($column)->byYear()`      |
