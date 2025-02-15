<?php

namespace ImageResizer\Service;

use ImageResizer\Interface\ConfigLoaderInterface;
use ImageResizer\Interface\ParserInterface;

class ConfigLoader implements ConfigLoaderInterface {
    private array $configs = [];

    public function __construct(private ParserInterface $parser, private string $filePath) {
        $this->loadConfig($parser, $filePath);
    }

    public function loadConfig(ParserInterface $parser, string $filePath): void {
        $this->resetConfigs();
        $parser->load($filePath);
        $this->configs = $parser->getAllValues();
    }

    public function getConfigValue(string $key): mixed {
        return $this->configs[$key] ?? null;
    }

    private function resetConfigs(): void {
        $this->configs = [];
    }
}
