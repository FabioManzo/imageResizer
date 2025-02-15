<?php

namespace ImageResizer\Service;

use ImageResizer\Interface\ConfigLoaderInterface;
use ImageResizer\Interface\ImageResizeInterface;

class ImageResizer implements ImageResizeInterface
{
    public function __construct(private ConfigLoaderInterface $config) {}

    public function resize(string $img, string $size): string
    {

        print_r($this->config);
        die;
    }
}
