<?php

namespace ImageResizer\Service\Processor;

class ParamProcessor implements TagProcessorInterface
{
    public function process(\SimpleXMLElement $element, array &$config): void {
        $name = (string) $element['name'];
        $value = isset($element['value']) ? (string) $element['value'] : (string) $element;

        if (!empty($name)) {
            if (str_ends_with($name, '[]')) {
                // Handle array parameters
                $baseName = substr($name, 0, -2);
                $config[$baseName][] = $value;
            } else {
                $config[$name] = $value;
            }
        }
    }
}
