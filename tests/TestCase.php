<?php

namespace AyupCreative\Features\Tests;

use AyupCreative\Features\FeaturesServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Queue;



abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
    }

    protected function getPackageProviders($app)
    {
        return [
            FeaturesServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        config()->set('features.default', false);
        config()->set('features.cache_ttl', 60);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
