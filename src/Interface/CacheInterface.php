<?php

namespace ImageResizer\Interface;

interface CacheInterface
{
    public function get(string $sourcePath, string $extension, string $sourceDir, callable $generateCallback, string $size = ""): string;
}
