# Between Dates

You can specify the start and end date of your trend data using the `->between()` method. This method will
return the trend data between the two dates you specify.

All dates should be Carbon-like instances (i.e. `\Carbon\Carbon`, `\Illuminate\Support\Carbon`, `\Carbon\CarbonImmutable`).

```php{6-9}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;
use Illuminate\Support\Carbon;

$metrics = Metrics::query(MyModel::query())
    ->between(
        Carbon::create('2025', '01', '01'), 
        Carbon::create('2025', '01', '31')
    )
    ->...
```

> [!NOTE]
> When calculating metrics by week, the start date will be adjusted to the start of the week that contains the start date,
> and the end date will be adjusted to the end of the week that contains the end date. This means it may include data outside
> of your specified range.
