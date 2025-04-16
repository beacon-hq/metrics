# Query Builders

Beacon Metrics builds upon a query builder to calculate metrics, this allows you
to easily add custom conditions to your queries.

Query builders can either be created using a function like `DB::table()` or `MyModel::query()`.

## Custom Query Conditions

You can add custom conditions by customizing the query builder before passing it to the `Metrics::query()` method.

```php
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$query = MyModel::query()
    ->where('category', 'Category Name'); // Get metrics for a specific category

$metrics = Metrics::query($query);
```

You can even use joins and subqueries to create more complex queries:

```php
use App\Models\MyModel;
use Beacon\Metrics\Metrics;

$query = MyModel::query()
    ->join('other_table', 'my_model.id', '=', 'other_table.my_model_id')
    ->where('other_table.category', 'Category Name');
    
$metrics = Metrics::query($query);
```

