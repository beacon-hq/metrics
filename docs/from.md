# From Date

If you want to calculate metrics from a specific date to the current time (to the second!) then you can use the `->from($date)` method.

The date should be Carbon-like instances (i.e. `\Carbon\Carbon`, `\Illuminate\Support\Carbon`, `\Carbon\CarbonImmutable`).

```php{6-8}
use App\Models\MyModel;
use Beacon\Metrics\Metrics;
use Illuminate\Support\Carbon;

$metrics = Metrics::query(MyModel::query())
    ->from(
        Carbon::create('2025', '01', '01')
    )
    ->...
```
