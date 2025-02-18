<?php

namespace Test\Dto;

class ResizedImageDimensions
{
    public function __construct(
        public int $cropWidth,
        public int $cropHeight,
        public int $newWidth,
        public int $newHeight,
        public int $srcX,
        public int $srcY,
    ) {}

    public function toArray(): array
    {
        return [
            $this->cropWidth,
            $this->cropHeight,
            $this->newWidth,
            $this->newHeight,
            $this->srcX,
            $this->srcY
        ];
    }
}