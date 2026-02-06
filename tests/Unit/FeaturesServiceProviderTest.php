<?php

namespace AyupCreative\Features\Tests\Unit;

use AyupCreative\Features\Evaluation\FeatureEvaluator;
use AyupCreative\Features\FeatureManager;
use AyupCreative\Features\Resolution\FeatureAttributeResolver;
use AyupCreative\Features\Tests\TestCase;

class FeaturesServiceProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_registers_singletons()
    {
        $this->assertInstanceOf(FeatureManager::class, $this->app->make(FeatureManager::class));
        $this->assertInstanceOf(FeatureEvaluator::class, $this->app->make(FeatureEvaluator::class));
    }

    public function test_it_merges_config()
    {
        $this->assertNotNull(config('features'));
        $this->assertEquals(false, config('features.default'));
    }
}
