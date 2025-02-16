<?php

namespace ImageResizer\Service;

use ImageResizer\Interface\ImageEditingLibraryInterface;

class GDlib implements ImageEditingLibraryInterface
{
    private LoggerService $logger;

    public function __construct()
    {
        $this->logger = LoggerService::getInstance();
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

        $originalWidth = $imageInfo[0];
        $originalHeight = $imageInfo[1];
        if ($newWidth === 0) {
            $ratio = $newHeight / $originalHeight;
            $newWidth = (int) round($originalWidth * $ratio);
            $this->logger->info("Width is *. New calculated width: $newWidth");
        } elseif ($newHeight === 0) {
            $ratio = $newWidth / $originalWidth;
            $newHeight = (int) round($originalHeight * $ratio);
            $this->logger->info("Height is *. New calculated height: $newHeight");
        }

        if ($crop) {
            // Central crop
            $scale = max($newWidth / $originalWidth, $newHeight / $originalHeight);
            $cropWidth = (int) round($newWidth / $scale);
            $cropHeight = (int) round($newHeight / $scale);
            $srcX = (int) round(($originalWidth - $cropWidth) / 2);
            $srcY = (int) round(($originalHeight - $cropHeight) / 2);
            $this->logger->info("Crop is true. newWidth: $newWidth, newHeight: $newHeight, srcX: $srcX, srcXY: $srcX");
        } else {
            // Adatta l'immagine senza crop
            $cropWidth = $originalWidth;
            $cropHeight = $originalHeight;
            $srcX = 0;
            $srcY = 0;
            $this->logger->info("Crop is false. newWidth: $newWidth, newHeight: $newHeight, srcX: $srcX, srcXY: $srcX");
        }

        // Create a new blank image with new dimensions
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        if ($imageInfo['mime'] === 'image/png' || $imageInfo['mime'] === 'image/gif') {
            imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
        }

        // Resize and copy the original image
        imagecopyresampled($newImage, $image, 0, 0, $srcX, $srcY, $newWidth, $newHeight, $cropWidth, $cropHeight);

        $this->applyFilters($newImage, $filters);

        // Save resized image
        $savedPath = $this->saveImageStrategy($newImage, $sourcePath, $cacheFolder, $size);

        // Free memory
        imagedestroy($image);
        imagedestroy($newImage);

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
                    $this->logger->info("Filter not recognized: $filter");
            }
        }
    }

    private function loadImageStrategy(string $sourcePath)
    {
        $imageExtention = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        return match ($imageExtention) {
            'jpg', 'jpeg' => \imagecreatefromjpeg($sourcePath),
            'gif' => \imagecreatefromgif($sourcePath),
            'png' => \imagecreatefrompng($sourcePath)
        };
    }

    private function saveImageStrategy($image, string $sourcePath, string $cacheFolder, $size): string
    {
        $imageExtention = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        $imageName = pathinfo($sourcePath, PATHINFO_BASENAME);
        $path = $cacheFolder . $size . "-" . $imageName;
        match ($imageExtention) {
            'jpg', 'jpeg' => \imagejpeg($image, $path),
            'gif' => \imagegif($image, $path),
            'png' => \imagepng($image, $path)
        };

        return $path;
    }
}
