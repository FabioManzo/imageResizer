<?php

namespace ImageResizer\Service;

use ImageResizer\Interface\CacheInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class FileCacheManager implements CacheInterface
{
    private FilesystemAdapter $cache;
    private string $cacheDir;
    private LoggerService $logger;

    public function __construct(
        private string $namespace = "",
        ?LoggerService $logger = null
    )
    {
        $this->cacheDir = getenv('CACHE_DIR');
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
        $this->logger = $logger ?? LoggerService::getInstance();
        $this->cache = new FilesystemAdapter($this->namespace, 0, $this->cacheDir);
    }

    public function get(string $sourcePath, callable $generateCallback): string
    {
        $cacheKey = md5($sourcePath);
        $sourcePathInConfigDir = getenv('CONFIG_PATH');
        $cacheItem = $this->cache->getItem($cacheKey);
        $cachedFilePath = "{$this->cacheDir}/$this->namespace/{$cacheKey}.json";
        if ($cacheItem->isHit() && file_exists($cachedFilePath)) {
            $this->logger->info("FILE_CACHE: File di configurazione '$sourcePath' trovato");
            $cacheTimestamp = filemtime($cachedFilePath);
            $sourceTimestamp = filemtime($sourcePathInConfigDir . $sourcePath);
            if ($cacheTimestamp >= $sourceTimestamp) {
                $this->logger->info("FILE_CACHE: File di configurazione '$cachedFilePath' valido. Lo uso (File di partenza: '$sourcePath')");
                return $cachedFilePath;
            }
            $this->logger->info("FILE_CACHE: File di configurazione '$sourcePath' NON valido. Lo rigenero");
        }

        // File regeneration delegated to the callback
        $generateCallback($sourcePath, $cachedFilePath);

        // Memorize file path in Symfony cache
        $cacheItem->set($cachedFilePath);
        $this->cache->save($cacheItem);

        return $cachedFilePath;
    }
}
