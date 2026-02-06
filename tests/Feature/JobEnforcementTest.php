<?php

namespace AyupCreative\Features\Tests\Feature;

use AyupCreative\Features\Attributes\DispatchWhenFeatureEnabled;
use AyupCreative\Features\Attributes\Feature;
use AyupCreative\Features\FeatureManager;
use AyupCreative\Features\Tests\TestCase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

use Illuminate\Contracts\Queue\ShouldQueue;

class JobEnforcementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // We want to test the actual service provider hook, 
        // but Queue::fake() might bypass it.
        // Let's try both.
    }

    public function test_job_dispatches_when_feature_enabled()
    {
        app(FeatureManager::class)->enable('feature-enabled');
        
        // This should not throw an exception
        JobWithFeature::dispatch();
        
        Queue::assertPushed(JobWithFeature::class);
    }

    public function test_job_does_not_dispatch_when_feature_disabled()
    {
        /**
         * BUG EXPOSED: This test currently FAILS because the dispatch-time enforcement 
         * in FeaturesServiceProvider is broken. It checks if the serialized job string 
         * is an object, which returns false, thus bypassing enforcement entirely.
         */
        // Restore real queue to test the hook
        Queue::swap(app('queue.connection'));

        app(FeatureManager::class)->disable('feature-disabled');
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Job [AyupCreative\Features\Tests\Feature\JobWithDisabledFeature] was not dispatched because its feature is disabled.');
        
        JobWithDisabledFeature::dispatch();
    }

    /**
     * This test specifically targets the reported bug: 
     * #[DispatchWhenFeatureEnabled] blocking dispatch even when enabled.
     */
    public function test_job_blocks_dispatch_even_when_enabled_REPRO()
    {
        app(FeatureManager::class)->enable('feature-enabled');

        // We use a job that has the attribute.
        // If the bug exists, this might throw an exception even though we enabled the feature.
        try {
            JobWithFeature::dispatch();
            $this->assertTrue(true); // Should reach here
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'was not dispatched because its feature is disabled')) {
                $this->fail('Job was blocked even though the feature was enabled! Bug reproduced.');
            }
            throw $e;
        }
    }

    public function test_multiple_dispatch_attributes_all_enabled()
    {
        app(FeatureManager::class)->enable('f1');
        app(FeatureManager::class)->enable('f2');
        
        JobWithMultipleFeatures::dispatch();
        Queue::assertPushed(JobWithMultipleFeatures::class);
    }

    public function test_multiple_dispatch_attributes_one_disabled()
    {
        app(FeatureManager::class)->enable('f1');
        app(FeatureManager::class)->disable('f2');
        
        $this->expectException(RuntimeException::class);
        JobWithMultipleFeatures::dispatch();
    }

    public function test_dispatch_methods()
    {
        app(FeatureManager::class)->enable('feature-enabled');
        
        // Job::dispatch()
        JobWithFeature::dispatch();
        
        // dispatch(new Job)
        dispatch(new JobWithFeature());
        
        // Queue::push()
        Queue::push(new JobWithFeature());
        
        Queue::assertPushed(JobWithFeature::class, 3);
    }
}

#[DispatchWhenFeatureEnabled('feature-enabled')]
class JobWithFeature implements ShouldQueue {
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;
    public function handle() {}
}

#[DispatchWhenFeatureEnabled('feature-disabled')]
class JobWithDisabledFeature implements ShouldQueue {
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;
    public function handle() {}
}

#[DispatchWhenFeatureEnabled('f1')]
#[DispatchWhenFeatureEnabled('f2')]
class JobWithMultipleFeatures implements ShouldQueue {
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;
    public function handle() {}
}
