<?php

/*
|--------------------------------------------------------------------------
| Features Flags
|--------------------------------------------------------------------------
|
| Can be used to determine whether a feature is enabled in situations throughout the application.
|
| $schedule->command(GenerateReport::class)
|   ->when(fn () => features()->enabled('reports-v2'));
|
*/

if(!function_exists('features')) {
    /**
     * Return the feature manager.
     *
     * @return \AyupCreative\Features\FeatureManager
     */
    function features(): \AyupCreative\Features\FeatureManager
    {
        return app(\AyupCreative\Features\FeatureManager::class);
    }
}
