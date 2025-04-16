# Min Aggregate

The min aggregate is used to get the minimum value of the values for a specific column.

## Usage

The `->min($column)` method is used to specify the min aggregate.

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->min('column_name')
    ->...
```

## Aliases

The `->min()` method has the following aliases:

| Alias                       | Equivalent                      |
|-----------------------------|---------------------------------|
| `->minBySecond($column)`    | `->min($column)->bySecond()`    |
| `->minByMinute($column)`    | `->min($column)->byMinute()`    |
| `->minByHour($column)`      | `->min($column)->byHour()`      |
| `->minByDay($column)`       | `->min($column)->byDay()`       |
| `->minByDayOfWeek($column)` | `->min($column)->byDayOfWeek()` |
| `->minByWeek($column)`      | `->min($column)->byWeek()`      |
| `->minByMonth($column)`     | `->min($column)->byMonth()`     |
| `->minByYear($column)`      | `->min($column)->byYear()`      |
