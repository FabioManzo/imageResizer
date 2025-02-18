<?php

namespace ImageResizer\Service;

use ImageResizer\Factory\CacheFactory;
use ImageResizer\Interface\CacheInterface;
use ImageResizer\Interface\ImageEditingLibraryInterface;

class GDlib implements ImageEditingLibraryInterface
{
    private string $namespace;
    private string $archivePath;
    private CacheInterface $cache;
    private LoggerService $logger;

    public function __construct(?LoggerService $logger = null)
    {
        $this->logger = $logger ?? LoggerService::getInstance();
        $this->namespace = getenv('CACHE_IMAGES') ?? "";
        $this->archivePath = getenv('ARCHIVE_PATH');
        $this->cache = CacheFactory::create($this->namespace);
    }

    public function resize(
        string $sourcePath,
        int $newWidth,
        int $newHeight,
        bool $crop,
        string $cacheFolder,
        string $size,
        array $filters = []
    ): string
    {
        $savedPath = $this->cache->get($sourcePath, "", $this->archivePath, function ($sourcePath, $cachePath) use (
            $newWidth, $newHeight, $crop, $cacheFolder, $size, $filters
        ) {
            return $this->resizeLogic($sourcePath, $newWidth, $newHeight, $crop, $cacheFolder, $size, $filters);
        });

        return $savedPath;
    }

    public function applyFilters($image, array $filters): void
    {
        foreach ($filters as $filter) {
            switch ($filter) {
                case 'BlackAndWhite':
                    imagefilter($image, IMG_FILTER_GRAYSCALE);
                    break;
                case 'FlipHorizontal':
                    imageflip($image, IMG_FLIP_HORIZONTAL);
                    break;
                default:
                    $this->logger->info("GDlib: Filter not recognized: $filter");
            }
        }
    }

    private function resizeLogic(
        string $sourcePath,
        int $newWidth,
        int $newHeight,
        bool $crop,
        string $cacheFolder,
        string $size,
        array $filters = []
    ): string {
        $path = getenv('ASSETS_PATH') . $sourcePath;
        $image = $this->loadImageStrategy($path);
        if (!$image) {
            throw new \RuntimeException("Failed to load image.");
        }

        $imageInfo = getimagesize($path);
        if ($imageInfo === false) {
            throw new \RuntimeException("Invalid image file: $path");
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($imageInfo['mime'], $allowedTypes, true)) {
            throw new \RuntimeException("Unsupported image format: " . $imageInfo['mime']);
        }

        list($cropWidth, $cropHeight, $srcX, $srcY, $newWidth, $newHeight) = $this->calculateNewDimensions(
            $imageInfo[0],
            $imageInfo[1],
            $newWidth,
            $newHeight,
            $crop
        );

        $newImage = $this->resizeImageResource($image, $srcX, $srcY, $cropWidth, $cropHeight, $newWidth, $newHeight);
        $this->applyFilters($newImage, $filters);
        return $this->saveImageStrategy($image, $sourcePath, $cacheFolder, $size);
    }

    private function calculateNewDimensions(
        int $originalWidth,
        int $originalHeight,
        int $newWidth,
        int $newHeight,
        bool $crop
    )
    {
        // If $originalWidth or $originalHeight are 0, set $crop = false
        $crop = $newWidth > 0 && $newHeight > 0 && $crop;
        if ($newWidth === 0) {
            $ratio = $newHeight / $originalHeight;
            $newWidth = (int) round($originalWidth * $ratio);
            $this->logger->info("GDlib: Width is $originalWidth. New calculated width: $newWidth");
        } elseif ($newHeight === 0) {
            $ratio = $newWidth / $originalWidth;
            $newHeight = (int) round($originalHeight * $ratio);
            $this->logger->info("GDlib: Height is $originalHeight. New calculated height: $newHeight");
        }

        if ($crop) {
            $scale = max($newWidth / $originalWidth, $newHeight / $originalHeight);
            $cropWidth = (int) round($newWidth / $scale);
            $cropHeight = (int) round($newHeight / $scale);
            $srcX = (int) round(($originalWidth - $cropWidth) / 2);
            $srcY = (int) round(($originalHeight - $cropHeight) / 2);
            $this->logger->info("GDlib: Crop is true. newWidth: $newWidth, newHeight: $newHeight, srcX: $srcX, srcXY: $srcX");
        } else {
            $cropWidth = $originalWidth;
            $cropHeight = $originalHeight;
            $srcX = 0;
            $srcY = 0;
            $this->logger->info("GDlib: Crop is false. newWidth: $newWidth, newHeight: $newHeight, srcX: $srcX, srcXY: $srcX");
        }

        return [$cropWidth, $cropHeight, $srcX, $srcY, $newWidth, $newHeight];
    }

    /*
     * Create a new \GdImage object
    */
    private function resizeImageResource(\GdImage $image, int $srcX, int $srcY, int $cropWidth, int $cropHeight, int $newWidth, int $newHeight): \GdImage
    {
        // creates a new, empty image resource
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        // Copies and Resizes the Source Image
        imagecopyresampled($newImage, $image, 0, 0, $srcX, $srcY, $newWidth, $newHeight, $cropWidth, $cropHeight);
        return $newImage;
    }

    private function loadImageStrategy(string $sourcePath): \GdImage
    {
        $imageExtention = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        return match ($imageExtention) {
            'jpg', 'jpeg' => \imagecreatefromjpeg($sourcePath),
            'gif' => \imagecreatefromgif($sourcePath),
            'png' => \imagecreatefrompng($sourcePath),
            default => throw new \InvalidArgumentException("File extention '$imageExtention' not supported")
        };
    }

    /**
     * Saves the image in cache
     */
    private function saveImageStrategy(\GdImage $image, string $sourcePath, string $cacheFolder, string $size): string
    {
        $path = $this->getPathWithSizeFromCache($sourcePath, $cacheFolder, $size);
        $imageExtention = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        match ($imageExtention) {
            'jpg', 'jpeg' => \imagejpeg($image, $path),
            'gif' => \imagegif($image, $path),
            'png' => \imagepng($image, $path),
            default => throw new \InvalidArgumentException("File extention '$imageExtention' not supported")
        };

        return $path;
    }

    private function getPathWithSizeFromCache(string $sourcePath, string $cacheFolder, string $size): string
    {
        $imageName = $this->getBaseName($sourcePath);
        return $cacheFolder . $this->namespace . "/" . $size . "-" . $imageName;
    }

    private function getBaseName(string $path): string
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }
}
