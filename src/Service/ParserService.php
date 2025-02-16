<?php

namespace ImageResizer\Service;

use ImageResizer\Factory\ParserFactory;
use ImageResizer\Interface\ParserInterface;

class ParserService
{
    public function getParser(string $parserString): ParserInterface
    {
        $factory = new ParserFactory();
        return $factory->createParser($parserString);
    }
}