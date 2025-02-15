<?php

namespace ImageResizer\Factory;

use ImageResizer\Service\Processor\GroupProcessor;
use ImageResizer\Service\Processor\ImportProcessor;
use ImageResizer\Service\Processor\ParamProcessor;
use ImageResizer\Service\Processor\TagProcessorInterface;

class TagProcessorFactory {
    public static function create(string $tag): TagProcessorInterface {
        return match ($tag) {
            'Import' => new ImportProcessor(),
            'Group' => new GroupProcessor(),
            'Param' => new ParamProcessor(),
            default => throw new \InvalidArgumentException("Unknown tag type: $tag"),
        };
    }
}
