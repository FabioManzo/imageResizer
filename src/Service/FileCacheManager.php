<?php

namespace ImageResizer\Service;

use ImageResizer\Interface\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class FileCacheManager implements CacheInterface
{
    private FilesystemAdapter $cache;
    private string $cacheDir;
    private string $assetsDir;
    private LoggerService $logger;

    public function __construct(
        private string $namespace = "",
        ?LoggerService $logger = null
    )
    {
        $this->cacheDir = getenv('CACHE_DIR');
        $this->assetsDir = getenv('ASSETS_PATH');
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
        $this->logger = $logger ?? LoggerService::getInstance();
        $this->cache = new FilesystemAdapter($this->namespace, 0, $this->cacheDir);
    }

    public function get(string $sourcePath, string $extension, string $sourceDir, callable $generateCallback, string $size = ""): string
    {
        $cacheKey = md5($size.$sourcePath);
        $cacheItem = $this->cache->getItem($cacheKey);
        $cachedFilePath = "{$this->cacheDir}/$this->namespace/{$cacheKey}.$extension";
        if ($cacheItem->isHit() && file_exists($cachedFilePath)) {
            $this->logger->info("FILE_CACHE: File '$sourcePath' trovato");
            $cacheTimestamp = filemtime($cachedFilePath);
            $completePath = $this->assetsDir . $sourceDir . $sourcePath;
            $sourceTimestamp = filemtime($completePath);
            if ($cacheTimestamp >= $sourceTimestamp) {
                $this->logger->info("FILE_CACHE: File '$cachedFilePath' valido. Lo uso (File di partenza: '$size.$sourcePath')");
                return $cachedFilePath;
            }
            $this->logger->info("FILE_CACHE: File '$sourcePath' NON valido. Lo rigenero");
        }
        // File regeneration delegated to the callback
        $generateCallback($sourcePath, $cachedFilePath);

        // Memorize file path in Symfony cache
        $cacheItem->set($cachedFilePath);
        $this->cache->save($cacheItem);
        return $cachedFilePath;
    }
}
