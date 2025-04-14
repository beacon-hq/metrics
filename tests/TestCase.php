<?php

declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use function Orchestra\Testbench\workbench_path;
use Staudenmeir\LaravelCte\DatabaseServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    public function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(workbench_path('database/migrations'));
    }

    protected function prop(object $object, string $property)
    {
        return (fn () => $this->{$property})->call($object);
    }

    protected function getPackageProviders($app)
    {
        return [
            DatabaseServiceProvider::class,
        ];
    }
}
