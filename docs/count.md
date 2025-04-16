# Count Aggregate

The count aggregate is used to count the number of records within the specified date range.

## Usage

The `->count()` method is used to specify the count aggregate. You can pass in the column name you want to count, the `id` column is used by default.

```php{5}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$metrics = Metrics::query(MyModel::query())
    ->count()
    ->...
```

## Aliases

The `->count()` method has the following aliases:

| Alias                  | Equivalent                 |
|------------------------|----------------------------|
| `->countBySecond()`    | `->count()->bySecond()`    |
| `->countByMinute()`    | `->count()->byMinute()`    |
| `->countByHour()`      | `->count()->byHour()`      |
| `->countByDay()`       | `->count()->byDay()`       |
| `->countByDayOfWeek()` | `->count()->byDayOfWeek()` |
| `->countByWeek()`      | `->count()->byWeek()`      |
| `->countByMonth()`     | `->count()->byMonth()`     |
| `->countByYear()`      | `->count()->byYear()`      |
