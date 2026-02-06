<?php

namespace AyupCreative\Features\Console;

use AyupCreative\Features\FeatureEvaluator;
use Illuminate\Console\Command;

abstract class FeatureAwareCommand extends Command
{
    public function handle()
    {
        $decision = app(FeatureEvaluator::class)->evaluate($this);

        if (!$decision->allowed) {
            $this->warn('This command is disabled by feature flags.');
            return self::FAILURE;
        }

        return $this->runCommand();
    }

    abstract protected function runCommand(): int;
}
