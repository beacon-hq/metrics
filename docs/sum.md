# Sum Aggregate

The sum aggregate is used to sum the value of records within the specified date range.

## Usage

The `->sum($column)` method is used to get the sum of values for a specific column. 

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->sum('column_name')
    ->...
```

## Aliases

The `->sum()` method has the following aliases:

| Alias                       | Equivalent                      |
|-----------------------------|---------------------------------|
| `->sumBySecond($column)`    | `->sum($column)->bySecond()`    |
| `->sumByMinute($column)`    | `->sum($column)->byMinute()`    |
| `->sumByHour($column)`      | `->sum($column)->byHour()`      |
| `->sumByDay($column)`       | `->sum($column)->byDay()`       |
| `->sumByDayOfWeek($column)` | `->sum($column)->byDayOfWeek()` |
| `->sumByWeek($column)`      | `->sum($column)->byWeek()`      |
| `->sumByMonth($column)`     | `->sum($column)->byMonth()`     |
| `->sumByYear($column)`      | `->sum($column)->byYear()`      |
