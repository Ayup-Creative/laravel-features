<?php

namespace AyupCreative\Features\Tests\Feature\Queue;

use AyupCreative\Features\Enforcement\Queue\FeatureGateJobMiddleware;
use AyupCreative\Features\FeatureManager;
use AyupCreative\Features\Tests\TestCase;
use AyupCreative\Features\Attributes\Feature;
use AyupCreative\Features\Attributes\Semantic\DelayWhenFeatureDisabled;
use AyupCreative\Features\Attributes\Semantic\FailWhenFeatureDisabled;
use AyupCreative\Features\Attributes\Semantic\SkipWhenFeatureDisabled;
use Illuminate\Support\Facades\DB;
use AyupCreative\Features\Resolution\FeatureAttributeResolver;

class FeatureGateJobMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Manual inclusion to bypass the autoloader bug
        if (!class_exists(FeatureAttributeResolver::class)) {
            require_once __DIR__ . '/../../../src/Evaluation/FeatureAttributeResolver.php';
        }
    }

    public function test_it_delays_job_when_delay_action_specified()
    {
        app(FeatureManager::class)->disable('test-feature');
        
        $job = new DelayJob();
        $middleware = new FeatureGateJobMiddleware();
        
        $middleware->handle($job, function () {
            $this->fail('Job should not be executed');
        });
        
        $this->assertTrue($job->released);
    }

    public function test_it_fails_job_when_fail_action_specified()
    {
        app(FeatureManager::class)->disable('test-feature');
        
        $job = new FailJob();
        $middleware = new FeatureGateJobMiddleware();
        
        $middleware->handle($job, function () {
            $this->fail('Job should not be executed');
        });
        
        $this->assertTrue($job->failed);
    }

    public function test_it_skips_job_when_skip_action_specified()
    {
        app(FeatureManager::class)->disable('test-feature');
        
        $job = new SkipJob();
        $middleware = new FeatureGateJobMiddleware();
        
        $result = $middleware->handle($job, function () {
            $this->fail('Job should not be executed');
        });
        
        $this->assertNull($result);
    }
}

#[Feature('test-feature')]
#[DelayWhenFeatureDisabled]
class DelayJob {
    public bool $released = false;
    public function release($delay) { $this->released = true; }
}

#[Feature('test-feature')]
#[FailWhenFeatureDisabled]
class FailJob {
    public bool $failed = false;
    public function fail() { $this->failed = true; }
}

#[Feature('test-feature')]
#[SkipWhenFeatureDisabled]
class SkipJob {}
