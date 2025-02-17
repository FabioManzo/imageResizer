<?php

namespace ImageResizer\Interface;

interface CacheInterface
{
    public function get(string $sourcePath, string $extension, callable $generateCallback): string;
}
