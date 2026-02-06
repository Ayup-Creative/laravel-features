<?php

namespace AyupCreative\Features\Tests\Feature\Http;

use AyupCreative\Features\Enforcement\Http\FeatureGateMiddleware;
use AyupCreative\Features\FeatureManager;
use AyupCreative\Features\Tests\TestCase;
use AyupCreative\Features\Attributes\Feature;
use Illuminate\Support\Facades\Route;

class FeatureGateMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_allows_request_if_no_feature_attribute()
    {
        Route::get('/no-feature', [NoFeatureController::class, 'index'])
            ->middleware(FeatureGateMiddleware::class);

        $response = $this->get('/no-feature');

        $response->assertStatus(200);
        $response->assertSee('ok');
    }

    public function test_it_aborts_if_controller_feature_disabled()
    {
        app(FeatureManager::class)->disable('controller-feature');

        Route::get('/controller-feature', [ControllerFeatureController::class, 'index'])
            ->middleware(FeatureGateMiddleware::class);

        $response = $this->get('/controller-feature');

        $response->assertStatus(404);
    }

    public function test_it_allows_if_controller_feature_enabled()
    {
        app(FeatureManager::class)->enable('controller-feature');

        Route::get('/controller-feature', [ControllerFeatureController::class, 'index'])
            ->middleware(FeatureGateMiddleware::class);

        $response = $this->get('/controller-feature');

        $response->assertStatus(200);
    }

    public function test_it_applies_method_level_feature_attributes()
    {
        app(FeatureManager::class)->disable('method-feature');

        Route::get('/method-feature', [MethodFeatureController::class, 'index'])
            ->middleware(FeatureGateMiddleware::class);

        $responseA = $this->get('/method-feature');

        app(FeatureManager::class)->enable('method-feature');

        $responseB = $this->get('/method-feature');

        $responseA->assertStatus(404);
        $responseB->assertStatus(200);
    }

    public function test_it_applies_controller_level_attributes_first()
    {
        app(FeatureManager::class)->disable('controller-feature');
        app(FeatureManager::class)->enable('method-feature');

        Route::get('/controller-feature', [ControllerAndMethodFeatureController::class, 'index'])
            ->middleware(FeatureGateMiddleware::class);

        $response = $this->get('/controller-feature');

        $response->assertStatus(404);
    }
}

class NoFeatureController {
    public function index() { return 'ok'; }
}

#[Feature('controller-feature')]
class ControllerFeatureController {
    public function index() { return 'ok'; }
}

class MethodFeatureController {
    #[Feature('method-feature')]
    public function index() { return 'ok'; }
}

#[Feature('controller-feature')]
class ControllerAndMethodFeatureController {

    #[Feature('method-feature')]
    public function index() { return 'ok'; }
}
