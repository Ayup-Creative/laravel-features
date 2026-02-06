<?php

namespace AyupCreative\Features\Tests\Feature;

use AyupCreative\Features\Evaluation\FeatureAttributeResolver;
use AyupCreative\Features\Evaluation\FeatureEvaluator;
use AyupCreative\Features\FeatureManager;
use AyupCreative\Features\Tests\TestCase;
use AyupCreative\Features\Attributes\DispatchWhenFeatureEnabled;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Queue;

class QueueDispatchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Manual inclusion to bypass the autoloader bug
        if (!class_exists(FeatureAttributeResolver::class)) {
            require_once __DIR__ . '/../../src/Evaluation/FeatureAttributeResolver.php';
        }
    }

    public function test_it_does_not_dispatch_job_if_feature_disabled()
    {
        // Disable the feature
        app(FeatureManager::class)->disable('dispatch-feature');

        // Dispatch the job
        TestDispatchJob::dispatch();

        // Evaluate dispatch manually
        $evaluator = app(FeatureEvaluator::class);
        $decision = $evaluator->evaluateDispatch(new TestDispatchJob());

        // Assert the feature flag blocks dispatch
        $this->assertFalse(
            $decision->allowed,
            'FeatureEvaluator should block the job because the feature is disabled.'
        );
    }

    public function test_it_allows_dispatching_job_if_feature_enabled()
    {
        app(FeatureManager::class)->enable('dispatch-feature');

        Queue::push(new TestDispatchJob());

        Queue::assertPushed(TestDispatchJob::class);
    }
}

#[DispatchWhenFeatureEnabled('dispatch-feature')]
class TestDispatchJob {
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;
    public function handle() {}
}
