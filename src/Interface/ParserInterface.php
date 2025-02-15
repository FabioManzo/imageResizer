<?php

namespace ImageResizer\Interface;

interface ParserInterface {
    public function load(string $filePath): void;
    public function getValue(string $xpath): mixed;
    public function getAllValues(): mixed;
}
