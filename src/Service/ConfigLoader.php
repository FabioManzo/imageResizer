<?php

namespace ImageResizer\Service;

use ImageResizer\Enum\ParserEnum;
use ImageResizer\Factory\CacheFactory;
use ImageResizer\Interface\CacheInterface;
use ImageResizer\Interface\ConfigLoaderInterface;
use ImageResizer\Interface\ParserInterface;
use ImageResizer\Service\PathResolver\ArrayPathResolver;
use ImageResizer\Service\PathResolver\PathResolverInterface;

class ConfigLoader implements ConfigLoaderInterface {
    private array $configs = [];
    private string $namespace;
    private CacheInterface $cache;
    private LoggerService $logger;
    private ParserInterface $parser;

    public function __construct(private string $filePath, ?ParserInterface $parser = null)
    {
        $this->namespace = getenv('CACHE_CONFIG') ?? "";
        $this->cache = CacheFactory::create($this->namespace);
        $this->logger = LoggerService::getInstance();
        $this->parser = $parser ?? $this->getParser();
        $this->load($this->parser, $this->filePath);
    }

    public function load(ParserInterface $parser, string $fileSourcePath): void
    {
        $cachedFilePath = $this->cache->get($fileSourcePath, "json", $this->namespace . "/", function ($sourcePath, $cachePath) use ($parser) {
            $this->logger->info("CONFIG_LOADER: Avvio parsing del file di configurazione '$sourcePath'");
            $parser->load($this->getPath() . $sourcePath);
            $this->configs = $parser->getAllValues();
            $json = json_encode($this->configs, JSON_PRETTY_PRINT);
            $this->logger->info("CONFIG_LOADER: Salvo file di configurazione '$sourcePath' in cache: '$cachePath'");
            file_put_contents($cachePath, $json);
        });

        $this->configs = json_decode(file_get_contents($cachedFilePath), true);
    }

    public function get(string $key, ?PathResolverInterface $pathResolver = null): mixed
    {
        if ($pathResolver === null) {
            $pathResolver = new ArrayPathResolver();
        }
        return $pathResolver->get($key, $this->configs);
    }

    public function getConfig(): array
    {
        return $this->configs;
    }

    public function getPath(): string
    {
        $configPath = getenv('CONFIG_PATH');
        if (!$configPath) {
            throw new \RuntimeException('CONFIG_PATH environment variable is not set.');
        }
        return $configPath;
    }

    private function getParser(?string $parserString = null): ParserInterface
    {
        $parserService = new ParserService();
        $parserString  = $parserString ?? ParserEnum::SimpleXmlParser->name;
        return $parserService->getParser($parserString);
    }
}
