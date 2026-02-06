<?php

namespace AyupCreative\Features\Events\Listeners;

use AyupCreative\Features\FeatureEvaluator;
use Illuminate\Queue\Listener;

abstract class FeatureAwareListener extends Listener
{
    public function handle($event): void
    {
        $decision = app(FeatureEvaluator::class)->evaluate($this);

        if (!$decision->allowed) {
            return;
        }

        $this->handleEvent($event);
    }

    abstract protected function handleEvent($event): void;
}
