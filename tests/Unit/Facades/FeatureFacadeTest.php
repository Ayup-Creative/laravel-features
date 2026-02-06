<?php

namespace AyupCreative\Features\Tests\Unit\Facades;

use AyupCreative\Features\Facades\Features;
use AyupCreative\Features\FeatureManager;
use AyupCreative\Features\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class FeatureFacadeTest extends TestCase
{
    public function test_it_can_be_used_as_a_facade()
    {
        // This will fail because the facade class is not found due to namespace/directory mismatch
        // AND because the service provider doesn't register the alias correctly (Features vs Features)

        DB::table('feature_flags')->insert(['key' => 'facade-feature', 'enabled' => true]);

        $this->assertTrue(Features::enabled('facade-feature'));
    }
}
