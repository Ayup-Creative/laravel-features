<?php

namespace AyupCreative\Features;

use AyupCreative\Features\Enforcement\Http\FeatureGateMiddleware;
use AyupCreative\Features\Enforcement\Queue\FeatureGateJobMiddleware;
use AyupCreative\Features\Evaluation\FeatureEvaluator;
use Illuminate\Contracts\Queue\Factory;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class FeaturesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/features.php',
            'features'
        );

        $this->app->singleton(FeatureEvaluator::class);
        $this->app->singleton(FeatureManager::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/features.php' => config_path('features.php'),
        ], 'features-config');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Dispatch-time enforcement
        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            $job = $payload['data']['command'] ?? null;

            if (is_string($job)) {
                try {
                    $job = unserialize($job);
                } catch (\Throwable) {
                    return [];
                }
            }

            if (!is_object($job)) {
                return [];
            }

            $decision = app(FeatureEvaluator::class)->evaluateDispatch($job);

            if (!$decision->allowed) {
                throw new RuntimeException(
                    sprintf(
                        'Job [%s] was not dispatched because its feature is disabled.',
                        get_class($job)
                    )
                );
            }

            return [];
        });

        // Global job middleware
        $this->app->afterResolving(
            Factory::class,
            fn($queue) => $queue->pushMiddleware(new FeatureGateJobMiddleware)
        );

        // HTTP middleware alias
        Route::aliasMiddleware('features', FeatureGateMiddleware::class);

        // Bus dispatch middleware
        $this->app->extend(\Illuminate\Contracts\Bus\Dispatcher::class, function ($dispatcher) {
            return new Enforcement\Queue\FeatureGateDispatcherDecorator($dispatcher);
        });

        $this->app->extend(\Illuminate\Bus\Dispatcher::class, function ($dispatcher) {
            return new Enforcement\Queue\FeatureGateDispatcherDecorator($dispatcher);
        });

        // Console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\EnableFeatureCommand::class,
                Console\Commands\DisableFeatureCommand::class,
                Console\Commands\ListFeaturesCommand::class,
            ]);
        }
    }
}
