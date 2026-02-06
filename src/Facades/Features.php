<?php

namespace AyupCreative\Features\Facades;

use AyupCreative\Features\FeatureManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool enabled(string $key)
 */
class Features extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FeatureManager::class;
    }
}
