<?php

namespace ImageResizer\Service\Processor;

use ImageResizer\Factory\TagProcessorFactory;

class GroupProcessor implements TagProcessorInterface
{
    public function process(\SimpleXMLElement $element, array &$config): void {
        $attributes = $element->attributes();
        $groupName = (string) $attributes['name'];
        if (!empty($groupName)) {
            $config['groups'][$groupName] = [];
            $namespace = $element->getNamespaces(true);
            $glzNamespace = $namespace['glz'];
            foreach ($element->children($glzNamespace) as $child) {
                $tag = $child->getName();
                $strategy = TagProcessorFactory::create($tag);
                $strategy->process($child, $config['groups'][$groupName]);
            }
        }
        dd(false);
    }
}
