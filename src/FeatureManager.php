<?php

namespace AyupCreative\Features;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FeatureManager
{
    protected static string $table = 'feature_flags';

    public function enabled(string $key): bool
    {
        return Cache::remember(
            static::featureKey($key),
            config('features.cache_ttl'),
            fn() => $this->resolve($key)
        );
    }

    protected static function featureKey(string $feature): string
    {
        return "feature_flag_{$feature}";
    }

    protected function resolve(string $key): bool
    {
        $default = config('features.default');

        $flag = DB::table(static::$table)->where('key', $key);

        if (!$flag->exists()) {
            $flag->insert(['key' => $key, 'enabled' => $default]);
            return $default;
        }

        return $flag->value('enabled') ?? $default;
    }

    public function enable(string $key): void
    {
        DB::table(static::$table)->updateOrInsert(
            ['key' => $key],
            ['enabled' => true, 'updated_at' => now()]
        );
        Cache::forget(static::featureKey($key));
    }

    public function disable(string $key): void
    {
        DB::table(static::$table)->updateOrInsert(
            ['key' => $key],
            ['enabled' => false, 'updated_at' => now()]
        );
        Cache::forget(static::featureKey($key));
    }

    public function all(): array
    {
        return DB::table(static::$table)->pluck('enabled', 'key')->toArray();
    }
}
