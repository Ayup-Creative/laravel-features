<?php

namespace AyupCreative\Features\Tests\Unit\Evaluation;

use AyupCreative\Features\Evaluation\FeatureDecision;
use AyupCreative\Features\Tests\TestCase;

class FeatureDecisionTest extends TestCase
{
    public function test_it_can_be_allowed()
    {
        $decision = FeatureDecision::allow();
        $this->assertTrue($decision->allowed);
        $this->assertEquals('abort', $decision->action);
    }

    public function test_it_can_be_denied()
    {
        $decision = FeatureDecision::deny('skip');
        $this->assertFalse($decision->allowed);
        $this->assertEquals('skip', $decision->action);
    }

    public function test_it_defaults_to_abort_on_deny()
    {
        $decision = FeatureDecision::deny();
        $this->assertFalse($decision->allowed);
        $this->assertEquals('abort', $decision->action);
    }
}
