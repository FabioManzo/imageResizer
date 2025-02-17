<?php

namespace ImageResizer\Service;

use ImageResizer\Interface\CacheInterface;

class FileCache implements CacheInterface {
    private string $cacheDir;
    private LoggerService $logger;

    public function __construct() {
        $this->cacheDir = getenv('CACHE_DIR');
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
        $this->logger = LoggerService::getInstance();
    }

    public function get(string $key, mixed $value): mixed
    {
        $filePath = $this->getFilePath($key);
        dump($filePath);
        if (!file_exists($filePath)) {
            $this->logger->info("CACHE: File {$key} not found");
            return null;
        }
        $cachedContent = file_get_contents($filePath);
        $cachedData = json_decode($cachedContent, true);
        $hash = $this->hash($value);
        if ($cachedData['hash'] !== $hash) {
            $this->logger->info("CACHE: File {$key} found but with old content");
            unlink($filePath); // Remove file with old content
            return null;
        }
        $this->logger->info("CACHE: File {$key} found");
        // @TODO: in caso di immagine, ritornare l'immagine e aggiungere anche il controllo se l'immagine esiste
        return $cachedData['value'];
    }

    public function set(string $key, mixed $value, mixed $valueToHash, int $ttl = 3600): void
    {
        $filePath = $this->getFilePath($key);
        $data = [
            'key' => $key,
            'hash' => $this->hash($valueToHash),
            'value' => $value
        ];
        file_put_contents($filePath, json_encode($data));
    }

    private function getFilePath(string $key): string
    {
        return "{$this->cacheDir}" . md5($key) . ".json";
    }

    private function hash(string $content): string
    {
        return md5($content);
    }
}
