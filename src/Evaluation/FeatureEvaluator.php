<?php

namespace AyupCreative\Features\Evaluation;

use AyupCreative\Features\Attributes\DispatchWhenFeatureEnabled;
use AyupCreative\Features\Attributes\Feature;
use AyupCreative\Features\Attributes\FeatureAll;
use AyupCreative\Features\Attributes\FeatureAny;
use AyupCreative\Features\Attributes\WhenFeatureDisabled;
use AyupCreative\Features\FeatureManager;

class FeatureEvaluator
{
    public function __construct(
        protected FeatureManager           $manager,
        protected FeatureAttributeResolver $resolver
    )
    {
    }

    public function evaluateDispatch(object $job): FeatureDecision
    {
        $attributes = $this->resolver->resolve($job);

        foreach ($attributes as $attr) {
            if ($attr instanceof DispatchWhenFeatureEnabled) {
                if (!$this->manager->enabled($attr->key)) {
                    return FeatureDecision::deny('skip');
                }
            }
        }

        return FeatureDecision::allow();
    }

    /**
     * Evaluate whether a job should be executed
     */
    public function evaluateExecution(object $job, ?string $method = null): FeatureDecision
    {
        if ($method === null && method_exists($job, 'handle')) {
            $method = 'handle';
        }

        $attributes = $this->resolver->resolve($job, $method);

        foreach ($attributes as $attr) {

            // Single feature
            if ($attr instanceof Feature) {
                if (!$this->manager->enabled($attr->key)) {
                    $action = $this->resolveDisabledAction($attributes);
                    return FeatureDecision::deny($action);
                }
            }

            // FeatureAll: all must be enabled
            if ($attr instanceof FeatureAll) {
                foreach ($attr->keys as $feature) {
                    if (!$this->manager->enabled($feature)) {
                        $action = $this->resolveDisabledAction($attributes);
                        return FeatureDecision::deny($action);
                    }
                }
            }

            // FeatureAny: at least one must be enabled
            if ($attr instanceof FeatureAny) {
                $anyEnabled = false;
                foreach ($attr->keys as $feature) {
                    if ($this->manager->enabled($feature)) {
                        $anyEnabled = true;
                        break;
                    }
                }

                if (!$anyEnabled) {
                    $action = $this->resolveDisabledAction($attributes);
                    return FeatureDecision::deny($action);
                }
            }
        }

        return FeatureDecision::allow();
    }

    /**
     * Determine the action to take when a feature is disabled
     */
    protected function resolveDisabledAction(array $attributes): string
    {
        return collect($attributes)
            ->first(fn($a) => $a instanceof WhenFeatureDisabled)
            ?->action ?? 'abort';
    }
}
