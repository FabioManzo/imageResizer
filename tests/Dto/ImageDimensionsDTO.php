<?php

namespace Test\Dto;

class ImageDimensionsDTO
{
    public function __construct(
        public int $originalWidth,
        public int $originalHeight,
        public int $newWidth,
        public int $newHeight,
        public bool $isCrop
    )
    {}
}
