<?php

namespace AyupCreative\Features\Tests\Feature;

use AyupCreative\Features\Attributes\Feature;
use AyupCreative\Features\FeatureManager;
use AyupCreative\Features\Tests\TestCase;
use Illuminate\Support\Facades\Route;

class MiddlewareEnforcementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        Route::get('/test-controller', [TestController::class, 'index'])->middleware('features');
        Route::get('/test-method', [TestController::class, 'methodOnly'])->middleware('features');
        Route::get('/test-combined', [TestController::class, 'combined'])->middleware('features');
    }

    public function test_controller_level_attribute_enabled()
    {
        $this->withoutExceptionHandling();
        app(FeatureManager::class)->enable('feature-controller');
        
        $this->get('/test-controller')
            ->assertStatus(200);
    }

    public function test_controller_level_attribute_disabled()
    {
        app(FeatureManager::class)->disable('feature-controller');
        
        $this->get('/test-controller')
            ->assertStatus(404);
    }

    public function test_method_level_attribute_enabled()
    {
        app(FeatureManager::class)->enable('feature-controller');
        app(FeatureManager::class)->enable('feature-method');
        
        $this->get('/test-method')
            ->assertStatus(200);
    }

    public function test_method_level_attribute_disabled()
    {
        app(FeatureManager::class)->enable('feature-controller');
        app(FeatureManager::class)->disable('feature-method');
        
        $this->get('/test-method')
            ->assertStatus(404);
    }

    public function test_combined_attributes_enabled()
    {
        app(FeatureManager::class)->enable('feature-controller');
        app(FeatureManager::class)->enable('feature-combined');
        
        $this->get('/test-combined')
            ->assertStatus(200);
    }

    public function test_combined_attributes_disabled()
    {
        app(FeatureManager::class)->enable('feature-controller');
        app(FeatureManager::class)->disable('feature-combined');
        
        $this->get('/test-combined')
            ->assertStatus(404);
    }
}

#[Feature('feature-controller')]
class TestController {
    public function index() { return 'ok'; }

    #[Feature('feature-method')]
    public function methodOnly() { return 'ok'; }

    #[Feature('feature-combined')]
    public function combined() { return 'ok'; }
}
