<?php

namespace AyupCreative\Features\Tests\Unit\Attributes;

use AyupCreative\Features\Attributes\LimitFeatureUsage;
use AyupCreative\Features\Tests\TestCase;

class LimitFeatureUsageTest extends TestCase
{
    public function test_it_can_be_instantiated()
    {
        $attr = new LimitFeatureUsage(max: 10, perSeconds: 60);
        $this->assertEquals(10, $attr->max);
        $this->assertEquals(60, $attr->perSeconds);
    }
}
