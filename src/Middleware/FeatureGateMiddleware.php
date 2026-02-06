<?php

namespace AyupCreative\Features\Middleware;

use AyupCreative\Features\FeatureEvaluator;

class FeatureGateMiddleware
{
    public function handle($request, $next)
    {
        $route = $request->route();
        [$controller, $method] = $route->getAction('controller')
            ? explode('@', $route->getAction('controller'))
            : [null, null];

        if ($controller) {
            $decision = app(FeatureEvaluator::class)
                ->evaluate($controller, $method);

            if (!$decision->allowed) {
                abort(404);
            }
        }

        return $next($request);
    }
}
