# Average Aggregate

The average aggregate is used to get the average of the values for a specific column.

## Usage

The `->average($column)` method is used to specify the average aggregate.

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->average('column_name')
    ->...
```

## Aliases

The `->average()` method has the following aliases:

| Alias                       | Equivalent                      |
|-----------------------------|---------------------------------|
| `->averageBySecond($column)`    | `->average($column)->bySecond()`    |
| `->averageByMinute($column)`    | `->average($column)->byMinute()`    |
| `->averageByHour($column)`      | `->average($column)->byHour()`      |
| `->averageByDay($column)`       | `->average($column)->byDay()`       |
| `->averageByDayOfWeek($column)` | `->average($column)->byDayOfWeek()` |
| `->averageByWeek($column)`      | `->average($column)->byWeek()`      |
| `->averageByMonth($column)`     | `->average($column)->byMonth()`     |
| `->averageByYear($column)`      | `->average($column)->byYear()`      |
