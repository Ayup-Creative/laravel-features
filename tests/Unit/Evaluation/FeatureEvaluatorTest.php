<?php

namespace AyupCreative\Features\Tests\Unit\Evaluation;

use AyupCreative\Features\Evaluation\FeatureAttributeResolver;
use AyupCreative\Features\Evaluation\FeatureEvaluator;
use AyupCreative\Features\FeatureManager;
use AyupCreative\Features\Tests\TestCase;
use AyupCreative\Features\Attributes\Feature;
use AyupCreative\Features\Attributes\FeatureAll;
use AyupCreative\Features\Attributes\FeatureAny;
use AyupCreative\Features\Attributes\DispatchWhenFeatureEnabled;
use AyupCreative\Features\Attributes\WhenFeatureDisabled;
use Mockery;

class FeatureEvaluatorTest extends TestCase
{
    protected FeatureEvaluator $evaluator;
    protected $manager;
    protected $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = Mockery::mock(FeatureManager::class);
        $this->resolver = Mockery::mock(FeatureAttributeResolver::class);
        $this->evaluator = new FeatureEvaluator($this->manager, $this->resolver);
    }

    public function test_it_allows_when_no_attributes_present()
    {
        $job = new \stdClass();
        $this->resolver->shouldReceive('resolve')
            ->with($job, null)
            ->andReturn([]);

        $decision = $this->evaluator->evaluateExecution($job);

        $this->assertTrue($decision->allowed);
    }

    public function test_it_denies_when_feature_disabled()
    {
        $job = new \stdClass();
        $this->resolver->shouldReceive('resolve')
            ->with($job, null)
            ->andReturn([
                new Feature('test-feature')
            ]);
        $this->manager->shouldReceive('enabled')->with('test-feature')->andReturn(false);

        $decision = $this->evaluator->evaluateExecution($job);

        $this->assertFalse($decision->allowed);
        $this->assertEquals('abort', $decision->action);
    }

    public function test_it_uses_custom_disabled_action()
    {
        $job = new \stdClass();
        $this->resolver->shouldReceive('resolve')
            ->with($job, null)
            ->andReturn([
                new Feature('test-feature'),
                new WhenFeatureDisabled('skip')
            ]);
        $this->manager->shouldReceive('enabled')->with('test-feature')->andReturn(false);

        $decision = $this->evaluator->evaluateExecution($job);

        $this->assertFalse($decision->allowed);
        $this->assertEquals('skip', $decision->action);
    }

    public function test_it_allows_dispatch_when_enabled()
    {
        $job = new \stdClass();
        $this->resolver->shouldReceive('resolve')
            ->with($job)
            ->andReturn([
                new DispatchWhenFeatureEnabled('test-feature')
            ]);
        $this->manager->shouldReceive('enabled')->with('test-feature')->andReturn(true);

        $decision = $this->evaluator->evaluateDispatch($job);

        $this->assertTrue($decision->allowed);
    }

    public function test_it_denies_dispatch_when_disabled()
    {
        $job = new \stdClass();
        $this->resolver->shouldReceive('resolve')
            ->with($job)
            ->andReturn([
                new DispatchWhenFeatureEnabled('test-feature')
            ]);
        $this->manager->shouldReceive('enabled')->with('test-feature')->andReturn(false);

        $decision = $this->evaluator->evaluateDispatch($job);

        $this->assertFalse($decision->allowed);
        $this->assertEquals('skip', $decision->action);
    }

    public function test_it_handles_feature_all_attribute()
    {
        $job = new \stdClass();
        $this->resolver->shouldReceive('resolve')
            ->with($job, null)
            ->andReturn([
                new FeatureAll(['feature-1', 'feature-2'])
            ]);

        $this->manager->shouldReceive('enabled')->with('feature-1')->andReturn(true);
        $this->manager->shouldReceive('enabled')->with('feature-2')->andReturn(false);

        $decision = $this->evaluator->evaluateExecution($job);

        $this->assertFalse($decision->allowed, 'FeatureAll should be evaluated but it is currently ignored.');
    }

    public function test_it_handles_feature_any_attribute()
    {
        $job = new \stdClass();
        $this->resolver->shouldReceive('resolve')
            ->with($job, null)
            ->andReturn([
                new FeatureAny(['feature-1', 'feature-2'])
            ]);

        $this->manager->shouldReceive('enabled')->with('feature-1')->andReturn(false);
        $this->manager->shouldReceive('enabled')->with('feature-2')->andReturn(false);

        $decision = $this->evaluator->evaluateExecution($job);

        $this->assertFalse($decision->allowed, 'FeatureAny should be evaluated but it is currently ignored.');
    }
}
