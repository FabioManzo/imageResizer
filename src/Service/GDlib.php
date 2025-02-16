<?php

namespace ImageResizer\Service;

use ImageResizer\Interface\ImageEditingLibraryInterface;

class GDlib implements ImageEditingLibraryInterface
{

    public function resize(string $sourcePath, int $newWidth, int $newHeight, string $cacheFolder): bool
    {
        // Load image
        $path = getenv('ASSETS_PATH') . $sourcePath;
        $image = $this->imageCreateStrategy($path);
        if (!$image) {
            throw new \RuntimeException("Failed to load image.");
        }

        $imageInfo = getimagesize($path);
        if ($imageInfo === false) {
            throw new \RuntimeException("Invalid image file: $path");
        }

        // Allowed MIME types
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (!in_array($imageInfo['mime'], $allowedTypes, true)) {
            throw new \RuntimeException("Unsupported image format: " . $imageInfo['mime']);
        }

        // Create a new blank image with new dimensions
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Resize and copy the original image
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $newWidth, $newHeight);

        // Save resized image
        $success = $this->saveImageStrategy($newImage, $sourcePath, $cacheFolder);

        // Free memory
        imagedestroy($image);
        imagedestroy($newImage);

        return $success;
    }

    private function imageCreateStrategy(string $sourcePath)
    {
        $imageExtention = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        return match ($imageExtention) {
            'jpg', 'jpeg' => \imagecreatefromjpeg($sourcePath),
            'gif' => \imagecreatefromgif($sourcePath),
            'png' => \imagecreatefrompng($sourcePath)
        };
    }

    private function saveImageStrategy($image, string $sourcePath, string $cacheFolder)
    {
        $imageExtention = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        $imageName = pathinfo($sourcePath, PATHINFO_BASENAME);
        $path = $cacheFolder . $imageName;
        return match ($imageExtention) {
            'jpg', 'jpeg' => \imagejpeg($image, $path),
            'gif' => \imagegif($image, $path),
            'png' => \imagepng($image, $path)
        };
    }
}
