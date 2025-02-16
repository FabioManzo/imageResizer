<?php

namespace ImageResizer\Factory;

use ImageResizer\Enum\ImageEditingLibraryEnum;
use ImageResizer\Interface\ImageEditingLibraryInterface;
use ImageResizer\Service\GDlib;

class ImageEditingLibraryFactory
{
    public static function create(string $libraryName): ?ImageEditingLibraryInterface
    {
        return match ($libraryName) {
            ImageEditingLibraryEnum::GDLIB->name  => new GDlib(),
            default => throw new \InvalidArgumentException("Library: {$libraryName} not supported"),
        };
    }
}
