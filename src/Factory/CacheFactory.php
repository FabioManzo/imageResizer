<?php

namespace ImageResizer\Factory;

use ImageResizer\Interface\CacheInterface;
use ImageResizer\Service\FileCacheManager;

class CacheFactory {
    public static function create(string $namespace = ""): CacheInterface
    {
        $cashDriver = getenv('CACHE_DRIVER');
        return match ($cashDriver) {
            'file'  => new FileCacheManager($namespace),
            default => throw new \Exception("Cache Driver '{$cashDriver}' is not supported"),
        };
    }
}
