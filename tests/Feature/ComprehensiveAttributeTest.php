<?php

namespace AyupCreative\Features\Tests\Feature;

use AyupCreative\Features\Attributes\Feature;
use AyupCreative\Features\Attributes\FeatureAll;
use AyupCreative\Features\Attributes\FeatureAny;
use AyupCreative\Features\Attributes\WhenFeatureDisabled;
use AyupCreative\Features\Evaluation\FeatureEvaluator;
use AyupCreative\Features\FeatureManager;
use AyupCreative\Features\Tests\TestCase;

class ComprehensiveAttributeTest extends TestCase
{
    private FeatureEvaluator $evaluator;
    private FeatureManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = app(FeatureEvaluator::class);
        $this->manager = app(FeatureManager::class);
    }

    public function test_feature_attribute_all_true()
    {
        $this->manager->enable('f1');
        
        $job = new #[Feature('f1')] class {};
        $decision = $this->evaluator->evaluateExecution($job);
        
        $this->assertTrue($decision->allowed);
    }

    public function test_feature_attribute_false()
    {
        $this->manager->disable('f1');
        
        $job = new #[Feature('f1')] class {};
        $decision = $this->evaluator->evaluateExecution($job);
        
        $this->assertFalse($decision->allowed);
        $this->assertEquals('abort', $decision->action);
    }

    public function test_feature_all_attribute_true()
    {
        $this->manager->enable('f1');
        $this->manager->enable('f2');
        
        $job = new #[FeatureAll(['f1', 'f2'])] class {};
        $decision = $this->evaluator->evaluateExecution($job);
        
        $this->assertTrue($decision->allowed);
    }

    public function test_feature_all_attribute_false()
    {
        $this->manager->enable('f1');
        $this->manager->disable('f2');
        
        $job = new #[FeatureAll(['f1', 'f2'])] class {};
        $decision = $this->evaluator->evaluateExecution($job);
        
        $this->assertFalse($decision->allowed);
    }

    public function test_feature_any_attribute_true()
    {
        $this->manager->disable('f1');
        $this->manager->enable('f2');
        
        $job = new #[FeatureAny(['f1', 'f2'])] class {};
        $decision = $this->evaluator->evaluateExecution($job);
        
        $this->assertTrue($decision->allowed);
    }

    public function test_feature_any_attribute_false()
    {
        $this->manager->disable('f1');
        $this->manager->disable('f2');
        
        $job = new #[FeatureAny(['f1', 'f2'])] class {};
        $decision = $this->evaluator->evaluateExecution($job);
        
        $this->assertFalse($decision->allowed);
    }

    public function test_custom_disabled_action()
    {
        $this->manager->disable('f1');
        
        $job = new #[Feature('f1'), WhenFeatureDisabled('skip')] class {};
        $decision = $this->evaluator->evaluateExecution($job);
        
        $this->assertFalse($decision->allowed);
        $this->assertEquals('skip', $decision->action);
    }

    public function test_combination_of_attributes_valid()
    {
        $this->manager->enable('f1');
        $this->manager->enable('f2');
        $this->manager->enable('f3');
        
        // Requires f1 AND (f2 OR f3)
        $job = new #[Feature('f1'), FeatureAny(['f2', 'f3'])] class {};
        $decision = $this->evaluator->evaluateExecution($job);
        
        $this->assertTrue($decision->allowed);
    }

    public function test_combination_of_attributes_invalid_mixed()
    {
        $this->manager->enable('f1');
        $this->manager->disable('f2');
        $this->manager->disable('f3');
        
        // Requires f1 AND (f2 OR f3). Here f1 is true, but (f2 OR f3) is false.
        $job = new #[Feature('f1'), FeatureAny(['f2', 'f3'])] class {};
        $decision = $this->evaluator->evaluateExecution($job);
        
        $this->assertFalse($decision->allowed, 'Should fail because FeatureAny requirement is not met.');
    }

    public function test_empty_arrays_in_attributes()
    {
        // FeatureAll with empty array - what should be the behavior? 
        // Current implementation: foreach doesn't run, so it stays allow if nothing else denies.
        $job = new #[FeatureAll([])] class {};
        $decision = $this->evaluator->evaluateExecution($job);
        $this->assertTrue($decision->allowed);

        $job2 = new #[FeatureAny([])] class {};
        $decision2 = $this->evaluator->evaluateExecution($job2);
        // Current implementation of FeatureAny: $anyEnabled = false, then if loop empty, $anyEnabled remains false, so it denies.
        $this->assertFalse($decision2->allowed, 'FeatureAny with empty array should deny by default.');
    }

    public function test_duplicate_features()
    {
        // This is expected to fail with a PHP Error because Feature is not repeatable
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('must not be repeated');
        
        $job = new #[Feature('f1'), Feature('f1')] class {};
        $this->evaluator->evaluateExecution($job);
    }

    public function test_multiple_when_feature_disabled_attributes()
    {
        // This currently fails with a PHP Error because WhenFeatureDisabled is not repeatable.
        // This highlights a gap where multiple actions cannot be defined or are restricted by PHP.
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('must not be repeated');
        
        $job = new #[Feature('f1'), WhenFeatureDisabled('skip'), WhenFeatureDisabled('fail')] class {};
        $this->evaluator->evaluateExecution($job);
    }

    public function test_attribute_inheritance()
    {
        $this->manager->disable('f1');
        $this->manager->enable('f2');
        
        $job = new ChildJob();
        $decision = $this->evaluator->evaluateExecution($job);
        
        /**
         * BUG EXPOSED: Attributes on parent classes are currently IGNORED.
         * If inheritance was supported, this should be denied because f1 is disabled.
         * But currently it allows because it only sees f2.
         */
        $this->assertFalse($decision->allowed, 'Attributes on parent classes should be respected.');
    }

    public function test_method_level_attributes_on_jobs_ignored()
    {
        $this->manager->disable('f-method');
        
        $job = new JobWithMethodAttribute();
        $decision = $this->evaluator->evaluateExecution($job);
        
        /**
         * BUG EXPOSED: Method-level attributes on jobs are currently IGNORED
         * because FeatureGateJobMiddleware does not pass the 'handle' method to the evaluator.
         */
        $this->assertFalse($decision->allowed, 'Method-level attributes on jobs should be respected.');
    }
}

#[Feature('f1')]
class ParentJob {}

#[Feature('f2')]
class ChildJob extends ParentJob {}

class JobWithMethodAttribute {
    #[Feature('f-method')]
    public function handle() {}
}
