<?php

namespace ImageResizer\Service\Processor;

class ParamProcessor implements TagProcessorInterface
{
    public function process(\SimpleXMLElement $element, array &$config): void {
        $attributes = array_values((array) $element->attributes())[0];
        $name = $attributes["name"];
        if (isset($attributes["value"])) {
            $value = $this->normalizeParamValue($attributes["value"]);
            if (str_ends_with($name, "[]")) {
                $config[str_replace("[]", "", $name)][] = $value;
            } else {
                $config[$name] = $value;
            }
        } else if ($name === 'longtext') {
            $config[$name] = trim((string) $element);
        }
    }

    private function normalizeParamValue(mixed $value):mixed
    {
        if (is_numeric($value)) {
            $value = (str_contains($value, '.')) ? (float) $value : (int) $value;
        } else if (in_array($value, ["true", "false"])) {
            $value = $value === "true";
        }

        return $value;
    }
}
