<?php

namespace ImageResizer\Interface;

interface ConfigLoaderInterface {
    public function loadConfig(ParserInterface $parser, string $filePath): void;
    public function getConfigValue(string $key): mixed;
}
