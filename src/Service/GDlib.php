<?php

namespace ImageResizer\Service;

use ImageResizer\Factory\CacheFactory;
use ImageResizer\Interface\CacheInterface;
use ImageResizer\Interface\ImageEditingLibraryInterface;

class GDlib implements ImageEditingLibraryInterface
{
    private string $namespace;
    private CacheInterface $cache;
    private LoggerService $logger;

    public function __construct()
    {
        $this->logger = LoggerService::getInstance();
        $this->namespace = getenv('CACHE_IMAGES') ?? "";
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
        $savedPath = $this->cache->get($sourcePath, function ($sourcePath, $cachePath) use (
            $newWidth, $newHeight, $crop, $cacheFolder, $size, $filters
        ) {
            $this->resizeLogic($newWidth, $newHeight, $crop, $cacheFolder, $size, $filters);
        });

        return $savedPath;




        /*
        - controlla se a quel path /cache/thumbnail-Dark_Side_of_the_Moon.jpg esiste l'immagine
            - se si,
                - prendi il json che è stato scritto con il nome di quell'immagine in md5
                - prendi la data di ultima modifica nel file e costruisci l'hash con nome-file + data del file trovato
                    - se corrispondono
                        - prendi l'immagine
                    - se NON corrispondono:
                        - genera un'altra immagine
                        - genera un altro json con la nuova data
            - se no,
                - genera un'immagine
                - genera un json con la nuova data
        */
        // Get the last modified time of the json corresponding to the image
        /*$pathWithSizeCached = $this->getPathWithSizeFromCache($sourcePath, $cacheFolder, $size);
        $lastModifiedCachedImage = file_exists($pathWithSizeCached) ? filemtime($pathWithSizeCached) : false;
        if ($lastModifiedCachedImage) {
            // The image exists in cache.
            $this->logger->info("GDlib: Json of the image $pathWithSizeCached found");
            // Read last modified time of the image from the corresponding json
            $imageName = $this->getBaseName($sourcePath);
            $imageFromCache = $this->cache->get($sourcePath, $imageName . $lastModifiedNewImage);
            //$imagePathWithSize = $this->getPathWithSizeFromCache($sourcePath, $cacheFolder, $size);
            dd("imagePathWithSize", $imagePathWithSize);
            $imageLastModified = filemtime($imagePathWithSize);
            dd($lastModified);
            // @TODO: Prendi il json dell'immagine, che come hash nomeFile + il timestamp di $lastModified
            //$this->cache->get();
        }*/


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
    )
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
            $this->logger->info("GDlib: Width is $originalWidth. New calculated width: $newWidth");
        } elseif ($newHeight === 0) {
            $ratio = $newWidth / $originalWidth;
            $newHeight = (int) round($originalHeight * $ratio);
            $this->logger->info("GDlib: Height is $originalHeight. New calculated height: $newHeight");
        }

        if ($crop) {
            // Central crop
            $scale = max($newWidth / $originalWidth, $newHeight / $originalHeight);
            $cropWidth = (int) round($newWidth / $scale);
            $cropHeight = (int) round($newHeight / $scale);
            $srcX = (int) round(($originalWidth - $cropWidth) / 2);
            $srcY = (int) round(($originalHeight - $cropHeight) / 2);
            $this->logger->info("GDlib: Crop is true. newWidth: $newWidth, newHeight: $newHeight, srcX: $srcX, srcXY: $srcX");
        } else {
            // Adatta l'immagine senza crop
            $cropWidth = $originalWidth;
            $cropHeight = $originalHeight;
            $srcX = 0;
            $srcY = 0;
            $this->logger->info("GDlib: Crop is false. newWidth: $newWidth, newHeight: $newHeight, srcX: $srcX, srcXY: $srcX");
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

        $pathWithSize = $this->getPathWithSizeFromCache($sourcePath, $cacheFolder, $size);
        $lastModifiedNewImage = filemtime($pathWithSize);
        $imageName = $this->getBaseName($sourcePath);

        //$this->cache->set($imageName, $imageName, $imageName . $lastModifiedNewImage);

        // Free memory
        imagedestroy($image);
        imagedestroy($newImage);

        return $savedPath;
    }

    private function loadImageStrategy(string $sourcePath)
    {
        $imageExtention = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        return match ($imageExtention) {
            'jpg', 'jpeg' => \imagecreatefromjpeg($sourcePath),
            'gif' => \imagecreatefromgif($sourcePath),
            'png' => \imagecreatefrompng($sourcePath),
            default => throw new \InvalidArgumentException("File extention '$imageExtention' not supported")
        };
    }

    private function saveImageStrategy($image, string $sourcePath, string $cacheFolder, string $size): string
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

    private function getPathWithSizeFromCache(string $sourcePath, string $cacheFolder, string $size, bool $getJsonExtention = false): string
    {
        $imageName = $this->getBaseName($sourcePath);
        if ($getJsonExtention) {
            $imageNameArr = explode(".", $imageName);
            array_pop($imageNameArr);
            $imageName = implode(".", $imageNameArr) . ".json";
        }
        return $cacheFolder . $this->namespace . "/" . $size . "-" . $imageName;
    }

    private function getBaseName(string $path): string
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }
}
