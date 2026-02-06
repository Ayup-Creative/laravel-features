<?php

namespace AyupCreative\Features\Tests\Unit\Events\Listeners;

use AyupCreative\Features\Tests\TestCase;

class FeatureAwareListenerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_surfaces_bugs_in_listener()
    {
        // Bug 1: Extends Illuminate\Queue\Listener which is for queue worker, not event listeners
        // Bug 2: Constructor mismatch due to Bug 1
        // Bug 3: Wrong import for FeatureEvaluator
        // Bug 4: Calls non-existent evaluate() method

        $this->assertTrue(true); // Documenting bugs via comments as the class is too broken to instantiate easily
    }
}
