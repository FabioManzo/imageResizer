<?php

namespace ImageResizer\interfaces;

interface XmlParserInterface {
    public function load(string $filePath): void;
    public function getValue(string $xpath): mixed;
}
