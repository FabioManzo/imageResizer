<?php

namespace ImageResizer\Interface;

interface ParserInterface {
    public function load(string $filePath): void;
    public function getAllValues(): mixed;
    public function getContent(): string;
}
