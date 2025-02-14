<?php

namespace ImageResizer\services;

use ImageResizer\interfaces\XmlParserInterface;

class ConfigLoader {
    private XmlParserInterface $parser;

    public function __construct(XmlParserInterface $parser) {
        $this->parser = $parser;
    }

    public function loadConfig(string $filePath): void {
        $this->parser->load($filePath);
    }

    public function getConfigValue(string $key): mixed {
        return $this->parser->getValue($key);
    }
}
