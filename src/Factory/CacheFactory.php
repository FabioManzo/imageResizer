<?php

namespace ImageResizer\Factory;

use ImageResizer\Interface\CacheInterface;
use ImageResizer\Service\FileCache;

class CacheFactory {
    public static function create(): ?CacheInterface
    {
        $cashDriver = getenv('CACHE_DRIVER');
        return match ($cashDriver) {
            'file'  => new FileCache(),
            default => null,
        };
    }
}
