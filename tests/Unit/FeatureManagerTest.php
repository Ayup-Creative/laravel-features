<?php

namespace AyupCreative\Features\Tests\Unit;

use AyupCreative\Features\FeatureManager;
use AyupCreative\Features\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class FeatureManagerTest extends TestCase
{
    protected FeatureManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new FeatureManager();
    }

    public function test_it_can_check_if_feature_is_enabled()
    {
        DB::table('feature_flags')->insert([
            'key' => 'test-feature',
            'enabled' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->assertTrue($this->manager->enabled('test-feature'));
    }

    public function test_it_can_check_if_feature_is_disabled()
    {
        DB::table('feature_flags')->insert([
            'key' => 'test-feature',
            'enabled' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->assertFalse($this->manager->enabled('test-feature'));
    }

    public function test_it_resolves_to_default_if_not_found_and_inserts_it()
    {
        config()->set('features.default', true);

        // Clear cache just in case
        Cache::forget('feature_flag_new-feature');

        $this->assertTrue($this->manager->enabled('new-feature'));

        $this->assertDatabaseHas('feature_flags', [
            'key' => 'new-feature',
            'enabled' => true,
        ]);
    }

    public function test_it_can_enable_a_feature()
    {
        $this->manager->enable('test-feature');

        $this->assertDatabaseHas('feature_flags', [
            'key' => 'test-feature',
            'enabled' => true,
        ]);
        
        $this->assertTrue($this->manager->enabled('test-feature'));
    }

    public function test_it_can_disable_a_feature()
    {
        $this->manager->enable('test-feature');
        $this->manager->disable('test-feature');

        $this->assertDatabaseHas('feature_flags', [
            'key' => 'test-feature',
            'enabled' => false,
        ]);

        $this->assertFalse($this->manager->enabled('test-feature'));
    }

    public function test_it_caches_the_result()
    {
        DB::table('feature_flags')->insert([
            'key' => 'cached-feature',
            'enabled' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // First call to cache it
        $this->assertTrue($this->manager->enabled('cached-feature'));

        // Manually change DB
        DB::table('feature_flags')->where('key', 'cached-feature')->update(['enabled' => false]);

        // Should still be true due to cache
        $this->assertTrue($this->manager->enabled('cached-feature'));
    }

    public function test_it_forgets_cache_when_enabling_or_disabling()
    {
        $this->manager->enable('toggle-feature');
        $this->assertTrue($this->manager->enabled('toggle-feature'));

        $this->manager->disable('toggle-feature');
        $this->assertFalse($this->manager->enabled('toggle-feature'));
        
        $this->manager->enable('toggle-feature');
        $this->assertTrue($this->manager->enabled('toggle-feature'));
    }

    public function test_it_returns_all_features()
    {
        $this->manager->enable('feature-1');
        $this->manager->disable('feature-2');

        $all = $this->manager->all();

        $this->assertArrayHasKey('feature-1', $all);
        $this->assertArrayHasKey('feature-2', $all);
        
        // SQLite returns 1/0 for booleans
        $this->assertEquals(1, $all['feature-1']);
        $this->assertEquals(0, $all['feature-2']);
    }
}
