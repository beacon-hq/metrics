<?php

declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Staudenmeir\LaravelCte\DatabaseServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
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
