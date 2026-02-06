<?php

namespace AyupCreative\Features\Tests\Feature;

use AyupCreative\Features\Enforcement\Queue\FeatureGateDispatcherDecorator;
use AyupCreative\Features\Tests\TestCase;

class DispatcherDecoratorTest extends TestCase
{
    public function test_dispatcher_is_decorated()
    {
        $dispatcher = app(\Illuminate\Contracts\Bus\Dispatcher::class);
        $this->assertInstanceOf(FeatureGateDispatcherDecorator::class, $dispatcher);
    }

    public function test_batch_method_exists_on_dispatcher()
    {
        $dispatcher = app(\Illuminate\Contracts\Bus\Dispatcher::class);

        // This should not throw an exception
        $dispatcher->batch([]);
        $this->assertTrue(true);
    }

    public function test_dispatchAfterResponse_method_enforces_features()
    {
        $dispatcher = app(\Illuminate\Contracts\Bus\Dispatcher::class);
        \AyupCreative\Features\Facades\Features::disable('feature-disabled');

        $this->expectException(\RuntimeException::class);
        $dispatcher->dispatchAfterResponse(new JobWithDecoratorDisabledFeature());
    }
}

#[\AyupCreative\Features\Attributes\DispatchWhenFeatureEnabled('feature-disabled')]
class JobWithDecoratorDisabledFeature
{
    use \Illuminate\Foundation\Bus\Dispatchable, \Illuminate\Foundation\Queue\Queueable;

    public function handle()
    {
    }
}
