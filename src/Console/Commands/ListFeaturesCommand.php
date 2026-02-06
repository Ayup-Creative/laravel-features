<?php

namespace AyupCreative\Features\Console\Commands;

use AyupCreative\Features\FeatureManager;
use Illuminate\Console\Command;

class ListFeaturesCommand extends Command
{
    protected $signature = 'features:list';
    protected $description = 'List all feature flags';

    public function handle(): void
    {
        $features = app(FeatureManager::class)->all();

        $this->table(
            [sprintf('%-20s', 'Features'), 'Status'],
            array_map(fn ($key, $enabled) => [
                sprintf('%-20s', $key),
                sprintf('%-6s', $enabled ? 'ON' : 'OFF')
            ], array_keys($features), $features),
        );
    }
}
