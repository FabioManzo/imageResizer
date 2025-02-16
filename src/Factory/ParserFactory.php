<?php

namespace ImageResizer\Factory;

use ImageResizer\Enum\ParserEnum;
use ImageResizer\Interface\ParserInterface;
use ImageResizer\Service\SimpleXmlParser;

class ParserFactory
{
    public function createParser(string $parserName): ParserInterface
    {
        return match ($parserName) {
            ParserEnum::SimpleXmlParser->name => new SimpleXmlParser(),
            default => throw new \InvalidArgumentException("Parser '$parserName' not supported"),
        };
    }
}