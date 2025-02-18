<?php

namespace ImageResizer\Factory;

use ImageResizer\Enum\ImageEditingLibraryEnum;
use ImageResizer\Interface\ImageEditingLibraryInterface;
use ImageResizer\Service\GDlib;
use ImageResizer\Service\ImageMagick;

class ImageEditingLibraryFactory
{
    public static function create(string $libraryName): ?ImageEditingLibraryInterface
    {
        return match ($libraryName) {
            ImageEditingLibraryEnum::GDLIB->name  => new GDlib(),
            ImageEditingLibraryEnum::ImageMagick->name  => new ImageMagick(),
            default => throw new \InvalidArgumentException("Library: {$libraryName} not supported"),
        };
    }
}
