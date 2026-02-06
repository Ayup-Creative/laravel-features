<?php

namespace AyupCreative\Features\Enforcement\Http;

use AyupCreative\Features\Evaluation\FeatureEvaluator;
use Closure;
use Illuminate\Http\Request;

class FeatureGateMiddleware
{
    public function __construct(
        protected FeatureEvaluator $evaluator
    )
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $controller = $request->route()->getController();
        $method = $request->route()->getActionMethod();

        if (!$this->evaluator->evaluateExecution($controller)->allowed) {
            return abort(404);
        }

        if (!$this->evaluator->evaluateExecution($controller, $method)->allowed) {
            return abort(404);
        }

        return $next($request);
    }
}
