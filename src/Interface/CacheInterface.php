<?php

namespace ImageResizer\Interface;

interface CacheInterface
{
    public function get(string $key, mixed $value): mixed;
    public function set(string $key, mixed $value, mixed $valueToHash, int $ttl = 3600): void;
}
