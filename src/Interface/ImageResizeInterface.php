<?php

namespace ImageResizer\Interface;

interface ImageResizeInterface {
    public function resize(string $img, string $size): string;
}
