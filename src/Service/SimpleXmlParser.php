<?php

namespace ImageResizer\Service;

use ImageResizer\Factory\TagProcessorFactory;
use ImageResizer\Interface\ParserInterface;

class SimpleXmlParser implements ParserInterface {
    private \SimpleXMLElement $xml;

    public function load(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: $filePath");
        }

        $this->xml = simplexml_load_file($filePath);
        if ($this->xml === false) {
            throw new \RuntimeException("Failed to parse XML file: $filePath");
        }
    }

    /*public function getValue(string $xpath): mixed {
        $result = $this->xml->xpath($xpath);
        return $result ? (string) $result[0] : null;
    }*/

    public function getAllValues(): array
    {
        $config = [];
        $namespace = $this->xml->getNamespaces(true);
        $glzNamespace = $namespace['glz'];
        foreach ($this->xml->children($glzNamespace) as $child) {
            $tag = $child->getName();
            $strategy = TagProcessorFactory::create($tag);
            $config = $strategy->process($child, $config);
        }

        return $config;
    }

    public function getContent(): string
    {
        return $this->xml->asXML();
    }
}
