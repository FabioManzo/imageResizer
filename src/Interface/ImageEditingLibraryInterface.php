<?php

namespace ImageResizer\Interface;

interface ImageEditingLibraryInterface
{
    public function resize(
        string $sourcePath,
        int $newWidth,
        int $newHeight,
        bool $crop,
        string $cacheFolder,
        string $size,
        array $filters = []
    ): string;

    public function applyFilters($image, array $filters): void;
}