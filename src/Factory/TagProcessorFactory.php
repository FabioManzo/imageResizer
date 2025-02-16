<?php

namespace ImageResizer\Factory;

use ImageResizer\Enum\GlzTagEnum;
use ImageResizer\Service\Processor\SimpleXML\GroupProcessor;
use ImageResizer\Service\Processor\SimpleXML\ImportProcessor;
use ImageResizer\Service\Processor\SimpleXML\ParamProcessor;
use ImageResizer\Service\Processor\SimpleXML\TagProcessorInterface;

class TagProcessorFactory {
    public static function create(string $tag): TagProcessorInterface
    {
        return match ($tag) {
            GlzTagEnum::Import->name => new ImportProcessor(),
            GlzTagEnum::Group->name => new GroupProcessor(),
            GlzTagEnum::Param->name => new ParamProcessor(),
            default => throw new \InvalidArgumentException("Unknown tag type: $tag"),
        };
    }
}
