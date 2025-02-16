<?php

namespace ImageResizer\Service;

use ImageResizer\Interface\ConfigLoaderInterface;
use ImageResizer\Interface\ParserInterface;
use ImageResizer\Service\PathResolver\ArrayPathResolver;
use ImageResizer\Service\PathResolver\PathResolverInterface;

class ConfigLoader implements ConfigLoaderInterface {
    private array $configs = [];

    public function __construct(private ParserInterface $parser, private string $filePath) {
        $this->load($parser, $filePath);
    }

    public function load(ParserInterface $parser, string $fileName): void {
        $parser->load($this->getPath() . $fileName);
        $this->configs = $parser->getAllValues();
    }

    public function get(string $key, ?PathResolverInterface $pathResolver = null): mixed {
        if ($pathResolver === null) {
            $pathResolver = new ArrayPathResolver();
        }
        return $pathResolver->get($key, $this->configs);
    }

    public function getConfig(): array {
        return $this->configs;
    }

    public function getPath(): string
    {
        $configPath = getenv('CONFIG_PATH');
        if (!$configPath) {
            throw new \RuntimeException('CONFIG_PATH environment variable is not set.');
        }
        return $configPath;
    }
}
