<?php

namespace ImageResizer\Service\Processor;

interface TagProcessorInterface
{
    public function process(\SimpleXMLElement $element, array &$config): void;
}