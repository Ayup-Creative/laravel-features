<?php

namespace AyupCreative\Features\Tests\Feature\Console;

use AyupCreative\Features\FeatureManager;
use AyupCreative\Features\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class FeatureCommandsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_can_enable_a_feature_via_command()
    {
        $this->artisan('features:enable', ['key' => 'command-feature'])
            ->expectsOutput('Features enabled.')
            ->assertExitCode(0);

        $this->assertTrue(app(FeatureManager::class)->enabled('command-feature'));
    }

    public function test_it_can_disable_a_feature_via_command()
    {
        app(FeatureManager::class)->enable('command-feature');

        $this->artisan('features:disable', ['key' => 'command-feature'])
            ->expectsOutput('Features disabled.')
            ->assertExitCode(0);

        $this->assertFalse(app(FeatureManager::class)->enabled('command-feature'));
    }

    public function test_it_lists_features()
    {
        app(FeatureManager::class)->enable('feature-a');
        app(FeatureManager::class)->disable('feature-b');

        $this->artisan('features:list')
            ->expectsOutput(sprintf('| %-20s | %-6s |', 'feature-a', 'ON'))
            ->expectsOutput(sprintf('| %-20s | %-6s |', 'feature-b', 'OFF'))
            ->assertExitCode(0);
    }

    public function test_it_enabled_all_features_via_command()
    {
        app(FeatureManager::class)->disable('feature-a');
        app(FeatureManager::class)->disable('feature-b');

        $this->artisan('features:enable --all')
            ->expectsOutput('All features enabled.')
            ->assertExitCode(0);

        $this->assertTrue(app(FeatureManager::class)->enabled('feature-a'));
        $this->assertTrue(app(FeatureManager::class)->enabled('feature-b'));
    }

    public function test_it_disabled_all_features_via_command()
    {
        app(FeatureManager::class)->enable('feature-a');
        app(FeatureManager::class)->enable('feature-b');

        $this->artisan('features:disable --all')
            ->expectsOutput('All features disabled.')
            ->assertExitCode(0);

        $this->assertFalse(app(FeatureManager::class)->enabled('feature-a'));
        $this->assertFalse(app(FeatureManager::class)->enabled('feature-b'));
    }

    public function test_error_when_all_flag_and_key_are_missing()
    {
        $this->artisan('features:enable')
            ->expectsOutput('You must specify a feature key or use the --all flag.')
            ->assertExitCode(1);
    }
}
