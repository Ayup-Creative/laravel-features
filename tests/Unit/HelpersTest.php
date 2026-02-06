<?php

namespace AyupCreative\Features\Tests\Unit;

use AyupCreative\Features\FeatureManager;
use AyupCreative\Features\Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_features_helper_returns_manager()
    {
        $this->assertInstanceOf(FeatureManager::class, features());
    }
}
