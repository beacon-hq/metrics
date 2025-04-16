# Model Integration

To make it easier to use, Beacon Metrics provides a `HasMetrics` trait that will
add a `metrics()` method to your model. This method will return a `Metrics` instance
with the model's query builder as the base query.

```php
use App\Models\MyModel;
use Beacon\Metrics\HasMetrics;

class MyModel extends Model
{
    use HasMetrics;
}

// Usage

$metrics = MyModel::metrics();
```

