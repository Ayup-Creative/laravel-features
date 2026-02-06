<?php

namespace AyupCreative\Features\Middleware\Jobs;

use AyupCreative\Features\FeatureEvaluator;

/**
 * Use on jobs to check for feature gate status.
 *
 * public function middleware()
 * {
 *      return [new FeatureGateJobMiddleware];
 * }
 */
class FeatureGateJobMiddleware
{
    public function handle($job, $next)
    {
        $decision = app(FeatureEvaluator::class)->evaluate($job);

        if (!$decision->allowed) {
            return match ($decision->action) {
                'skip' => null,
                'fail' => $job->fail(),
                'delay' => $job->release(300),
                default => null,
            };
        }

        $next($job);
    }
}
