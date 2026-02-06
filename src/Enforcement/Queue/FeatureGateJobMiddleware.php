<?php

namespace AyupCreative\Features\Enforcement\Queue;

use AyupCreative\Features\Evaluation\FeatureEvaluator;

class FeatureGateJobMiddleware
{
    public function handle($job, $next)
    {
        $decision = app(FeatureEvaluator::class)->evaluateExecution($job, 'handle');

        if (!$decision->allowed) {
            return match ($decision->action) {
                'delay' => $job->release(300),
                'fail' => $job->fail(),
                'skip' => null,
                default => null,
            };
        }

        return $next($job);
    }
}
