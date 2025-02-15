<?php

namespace ImageResizer\Service\Processor;

class ImportProcessor implements TagProcessorInterface
{
    public function process(\SimpleXMLElement $element, array &$config): void {
        $attributes = $element->attributes();
        $src = (string) $attributes['src'];
        if (!empty($src)) {
            $config['imports'][] = $src;
        }
    }
}
