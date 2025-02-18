<?php

namespace ImageResizer\Interface;

interface CacheInterface
{
    public function get(string $sourcePath, callable $generateCallback): string;
}
