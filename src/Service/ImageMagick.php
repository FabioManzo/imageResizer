<?php

namespace ImageResizer\Service;

use ImageResizer\Interface\ImageEditingLibraryInterface;

class ImageMagick implements ImageEditingLibraryInterface
{

    public function resize(string $sourcePath, int $newWidth, int $newHeight, bool $crop, string $cacheFolder, string $size, array $filters = []): string
    {
        // TODO: Implement resize() method.
        return "";
    }

    public function applyFilters($image, array $filters): void
    {
        // TODO: Implement applyFilters() method.
    }
}