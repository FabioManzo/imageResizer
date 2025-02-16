<?php

namespace ImageResizer\Service;

use ImageResizer\Factory\ImageEditingLibraryFactory;
use ImageResizer\Interface\ConfigLoaderInterface;
use ImageResizer\Interface\ImageEditingLibraryInterface;
use ImageResizer\Interface\ImageResizeInterface;

class ImageResizer implements ImageResizeInterface
{
    private ImageEditingLibraryInterface $imageEditingLibrary;

    public function __construct(private ConfigLoaderInterface $config) {
        if (!$config->get("mode")) {
            throw new \InvalidArgumentException("The configuration object does not contain a 'mode' param");
        }
        $this->imageEditingLibrary = ImageEditingLibraryFactory::create($config->get("mode"));
    }

    public function resize(string $img, string $size): string
    {
        $sizeArr = $this->config->get($size);
        if (!isset($sizeArr['width']) || !isset($sizeArr['height'])) {
            throw new \InvalidArgumentException("Both 'width' and 'height' param must be present. Size loaded: '$size', values: : " . json_encode($sizeArr));
        }
        $archive = $this->config->get("archive");
        $cacheFolder = $this->config->get("imageCache");
        $width = $sizeArr['width'] === "*" ? 0 : $sizeArr['width'];
        $height = $sizeArr['height'] === "*" ? 0 : $sizeArr['height'];
        $crop = $sizeArr['width'] === "*" || $sizeArr['height'] === "*" ? true : $sizeArr['crop'];
        $filters = $sizeArr['filters'] ?? [];
        return $this->imageEditingLibrary->resize($archive . $img, $width, $height, $crop, $cacheFolder, $size, $filters);
    }
}
