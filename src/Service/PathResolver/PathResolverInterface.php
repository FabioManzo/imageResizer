<?php

namespace ImageResizer\Service\PathResolver;

interface PathResolverInterface
{
    public function get(string $path, array $array): mixed;
}
