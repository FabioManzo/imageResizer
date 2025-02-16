<?php

namespace ImageResizer\Service\Processor\SimpleXML;

interface TagProcessorInterface
{
    public function process(\SimpleXMLElement $element, array &$config): void;
}