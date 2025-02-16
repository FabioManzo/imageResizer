<?php

namespace ImageResizer\Interface;

use ImageResizer\Service\PathResolver\PathResolverInterface;

interface ConfigLoaderInterface {
    public function getPath(): string;
    public function load(ParserInterface $parser, string $fileName): void;
    public function get(string $key, ?PathResolverInterface $pathResolver = null): mixed;
    public function getConfig(): array;
}
