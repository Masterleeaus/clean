<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

trait HasCacheFirst
{
    public static function getCache()
    {
        $model = new static;

        if (app()->runningInConsole() && ! Schema::hasTable($model->getTable())) {
            return $model;
        }

        return Cache::remember(static::$cacheKey, static::$cacheTtl, static function () use ($model) {
            return static::query()->first() ?? $model;
        });
    }

    public static function forgetCache(): void
    {
        Cache::forget(static::$cacheKey);
    }
}
