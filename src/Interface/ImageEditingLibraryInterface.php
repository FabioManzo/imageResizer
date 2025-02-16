<?php

namespace ImageResizer\Interface;

interface ImageEditingLibraryInterface
{
    public function resize(string $sourcePath, int $newWidth, int $newHeight, string $cacheFolder): bool;
}