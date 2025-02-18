<?php

namespace ImageResizer\Service\Processor\SimpleXML;

use ImageResizer\Enum\ParserEnum;
use ImageResizer\Service\ConfigLoader;
use ImageResizer\Service\ParserService;

class ImportProcessor implements TagProcessorInterface
{
    public function process(\SimpleXMLElement $element, array $config): array {
        $attributes = $element->attributes();
        $src = (string) $attributes['src'];
        if (empty($src)) {
            throw new \InvalidArgumentException("The 'src' attribute is missing from the <glz:Import> tag.");
        }
        $parserService = new ParserService();
        $parser = $parserService->getParser(ParserEnum::SimpleXmlParser->name);
        $configLoader = new ConfigLoader($src, $parser);
        $importedConfig = $configLoader->getConfig();
        $config = array_merge($config, $importedConfig);

        return $config;
    }
}
