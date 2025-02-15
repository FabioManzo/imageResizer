<?php

namespace ImageResizer\Service;

use ImageResizer\Interface\ParserInterface;

class SimpleXmlParser implements ParserInterface {
    private \SimpleXMLElement $xml;

    public function load(string $filePath): void {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File not found: $filePath");
        }

        $this->xml = simplexml_load_file($filePath);
        if ($this->xml === false) {
            throw new \RuntimeException("Failed to parse XML file: $filePath");
        }
    }

    public function getValue(string $xpath): mixed {
        $result = $this->xml->xpath($xpath);
        return $result ? (string) $result[0] : null;
    }

    public function getAllValues(): array {
        $values = [];

        // Iterate through elements and get values
        $namespace = $this->xml->getNamespaces(true);
        $glzNamespace = $namespace['glz']; // Assuming 'glz' is the correct prefix

        foreach ($this->xml->children($glzNamespace) as $child) {
            $values[$child->getName()] = (string) $child->ge;
        }

        // Iterate through attributes and get values
        foreach ($this->xml->attributes() as $name => $value) {
            dd($name, $value);
            $values["@{$name}"] = (string) $value;
        }

        dd($values);

        return $values;
    }
}
