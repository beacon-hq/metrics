<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

pest()->extend(TestCase::class)->in('Unit', 'Feature')
    ->beforeEach(function () {
        Config::set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        Config::set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => env('MYSQL_HOST', '127.0.0.1'),
            'port' => env('MYSQL_PORT', '3306'),
            'database' => env('MYSQL_DATABASE', 'metrics'),
            'username' => env('MYSQL_USERNAME', 'root'),
            'password' => env('MYSQL_PASSWORD', ''),
            'prefix' => '',
        ]);

        Config::set('database.connections.pgsql', [
            'driver' => 'pgsql',
            'host' => env('POSTGRES_HOST', '127.0.0.1'),
            'port' => env('POSTGRES_PORT', '5433'),
            'database' => env('POSTGRES_DATABASE', 'postgres'),
            'username' => env('POSTGRES_USERNAME', 'postgres'),
            'password' => env('POSTGRES_PASSWORD', 'postgres'),
            'prefix' => '',
        ]);
    });

dataset('databases', function () {
    $databases = [
        ['sqlite'],
    ];
    if (! empty(getenv('MYSQL_HOST'))) {
        $databases[] = ['mysql'];
    }

    if (! empty(getenv('POSTGRES_HOST'))) {
        $databases[] = ['pgsql'];
    }

    return $databases;
});

function createTestSchema($db)
{
    Config::set('database.default', $db);

    DB::getSchemaBuilder()->dropAllTables();
    DB::getSchemaBuilder()->create('test_data', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->float('value');
        $table->string('category');
        $table->timestamps();

        $table->index(['category', 'value']);
        $table->index(['category', 'created_at']);
        $table->index('created_at');
    });
}

function createTestData($db)
{
    CarbonImmutable::setTestNow(CarbonImmutable::create(2025, 04, 10, 2, 38, 15));

    createTestSchema($db);

    $dates = [
        // Last year data
        CarbonImmutable::now()->subYear()->subMonths(2),
        CarbonImmutable::now()->subYear()->subMonths(1),
        CarbonImmutable::now()->subYear(),

        // This year data (different months)
        CarbonImmutable::now()->subMonths(3),
        CarbonImmutable::now()->subMonths(2),
        CarbonImmutable::now()->subMonths(1),

        // This month data (different weeks)
        CarbonImmutable::now()->subWeeks(3),
        CarbonImmutable::now()->subWeeks(2),
        CarbonImmutable::now()->subWeeks(1),

        // This week data (different days)
        CarbonImmutable::now()->subDays(4),
        CarbonImmutable::now()->subDays(3),
        CarbonImmutable::now()->subDays(2),
        CarbonImmutable::now()->subDays(1),

        // This day data (different hours)
        CarbonImmutable::now()->subHours(4),
        CarbonImmutable::now()->subHours(3),
        CarbonImmutable::now()->subHours(2),
        CarbonImmutable::now()->subHours(1),

        // This hour data (different minutes)
        CarbonImmutable::now()->subMinutes(4),
        CarbonImmutable::now()->subMinutes(3),
        CarbonImmutable::now()->subMinutes(2),
        CarbonImmutable::now()->subMinutes(1),

        // This minute data (different seconds)
        CarbonImmutable::now()->subSeconds(4),
        CarbonImmutable::now()->subSeconds(3),
        CarbonImmutable::now()->subSeconds(2),
        CarbonImmutable::now()->subSeconds(1),

        CarbonImmutable::now(),
    ];

    // Insert records with different categories
    $categories = ['category1', 'category2', 'category3'];

    if (DB::connection()->getDriverName() === 'mysql' || DB::connection()->getDriverName() === 'mariadb') {
        DB::statement('SET time_zone = \'UTC\'');
    }

    /** @var CarbonImmutable $date */
    foreach ($dates as $index => $date) {
        $category = $categories[$index % count($categories)];
        $value = ($index + 1) * 10;

        DB::table('test_data')->insert([
            'name' => "Item $index",
            'value' => $value,
            'category' => $category,
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }
}
