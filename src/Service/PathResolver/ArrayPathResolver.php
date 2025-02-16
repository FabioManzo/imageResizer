<?php

namespace ImageResizer\Service\PathResolver;

class ArrayPathResolver implements PathResolverInterface
{
    public function get(string $path, array $value): mixed
    {
        $keys = explode('/', $path);

        foreach ($keys as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                throw new \InvalidArgumentException("Key '$path' not found in configuration");
            }
        }

        return $value;
    }
}
