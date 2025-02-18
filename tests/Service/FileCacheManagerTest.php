<?php

namespace Test\Service;

use ImageResizer\Service\FileCacheManager;
use ImageResizer\Service\LoggerService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 *  On cache miss, it must:
 *    - invoke the callback with the expected params
 *    - save the key in cache (md5 of the filename)
 *  On cache hit, it must:
 *    - check if the json path in cache:
 *       - exists as a file (the callback generates the json with its own logic)
 *       - is not older than the file to generate (sourceFile)
 *    - NOT invoke the callback
 *  In both cases, it must:
 *    - return the path to the cached json
**/
class FileCacheManagerTest extends TestCase
{
    private string $cacheDirectory;

    protected function setUp(): void
    {
        $this->cacheDirectory = getenv('CACHE_DIR');
        // Ensure the cache directory is clean before each test
        $filesystem = new Filesystem();
        if ($filesystem->exists($this->cacheDirectory)) {
            $filesystem->remove($this->cacheDirectory);
        }
        $filesystem->mkdir($this->cacheDirectory);
    }

    public function testGetCacheMissAndHit()
    {
        // Ignore logs
        $mockLogger = $this->createMock(LoggerService::class);
        $mockLogger->expects($this->any())
            ->method('info')
            ->willReturnCallback(function () {
                // Simulates the logging behavior but does nothing.
            });
        ;

        $fileManager = new FileCacheManager("config", $mockLogger);
        $sourcePath = "myConfig.xml";
        $expectedCacheKey = md5($sourcePath);
        $expectedCachedFilePath = "cache/config/{$expectedCacheKey}.json";

        // The mocked callable must be called once with exact parameters
        $mockCallable = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['__invoke']) // Allow mocking a callable
            ->getMock();
        $mockCallable->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->equalTo($sourcePath),
                $this->equalTo($expectedCachedFilePath)
            )->willReturnCallback(function (string $param1, string $param2) {
                // Simulate file generation
                file_put_contents($param2, json_encode(['mock' => 'data']));
            });

        // Execute the method with the mock callable
        $pathGeneratedFromCallback = $fileManager->get($sourcePath, "json", "config", $mockCallable);

        // Assert that get() returned the expected cache file path
        $this->assertSame($expectedCachedFilePath, $pathGeneratedFromCallback);

        // Mocked callable should not be called the second time if cached file is not expired
        $cachedPathVal = $fileManager->get($sourcePath, "json", "config", $mockCallable);

        $this->assertSame($expectedCachedFilePath, $cachedPathVal);
    }

    public function testGetCacheRegenerationOnCacheExpiry()
    {
        // Ignore logs
        $mockLogger = $this->createMock(LoggerService::class);
        $mockLogger->expects($this->any())
            ->method('info')
            ->willReturnCallback(function () {
                // Simulates the logging behavior but does nothing.
            });

        $fileManager = new FileCacheManager("config", $mockLogger);
        $sourcePath = "myConfig.xml";
        $expectedCacheKey = md5($sourcePath);
        $expectedCachedFilePath = "cache/config/{$expectedCacheKey}.json";

        // The mocked callable must be called once with exact parameters
        $mockCallable = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['__invoke']) // Allow mocking a callable
            ->getMock();
        $mockCallable->expects($this->exactly(2))
            ->method('__invoke')
            ->with(
                $this->equalTo($sourcePath),
                $this->equalTo($expectedCachedFilePath)
            )->willReturnCallback(function (string $param1, string $param2) {
                // Simulate file generation
                file_put_contents($param2, json_encode(['mock' => 'data']));
            });

        // Make the get method save the item in cache
        $fileManager->get($sourcePath, "json", "config", $mockCallable);

        // Simulate a file change (Cached file is older than the sourceFile $sourcePath) to trigger the regeneration
        touch($expectedCachedFilePath, strtotime('-1 year'));

        // The file is present in cache but it is older than the sourceFile, so the callback gets called again to generate it
        $fileManager->get($sourcePath, "json", "config", $mockCallable);

        // The file has been regenerated, so we have a cache hit and the callback does NOT get called a third time
        $fileManager->get($sourcePath, "json", "config", $mockCallable);
    }

    public function testGetCacheMissAndHitImage()
    {
        // Ignore logs
        $mockLogger = $this->createMock(LoggerService::class);
        $mockLogger->expects($this->any())
            ->method('info')
            ->willReturnCallback(function () {
                // Simulates the logging behavior but does nothing.
            });
        ;
        $namespace = "images";
        $fileManager = new FileCacheManager($namespace, $mockLogger);
        $sourcePath = "sample.gif";
        $expectedCacheKey = md5($sourcePath);
        $extension = "gif";
        $expectedCachedFilePath = "cache/$namespace/{$expectedCacheKey}.$extension";

        // The mocked callable must be called once with exact parameters
        $mockCallable = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['__invoke']) // Allow mocking a callable
            ->getMock();
        $mockCallable->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->equalTo($sourcePath),
                $this->equalTo($expectedCachedFilePath)
            )->willReturnCallback(function (string $fileName, string $cachedImageDestinationPath) {
                // Simulate file generation
                $this->saveMockImage($fileName, $cachedImageDestinationPath);
            });

        // Execute the method with the mock callable
        $fileManager->get($sourcePath, $extension, "archive", $mockCallable);

        // Mocked callable should not be called the second time if cached file is not expired
        $cachedPathVal = $fileManager->get($sourcePath, $extension, "archive", $mockCallable);
        $this->assertSame($expectedCachedFilePath, $cachedPathVal);
    }

    protected function saveMockImage(string $filename, string $destinationPath)
    {
        $imagesPath = getenv('ASSETS_PATH') . "archive/";
        $sourcePath = $imagesPath . $filename;

        // Read the image file
        $imageData = file_get_contents($sourcePath);

        if ($imageData === false) {
            die("Error: Unable to read the source image.");
        }

        // Save the image to the new location
        if (file_put_contents($destinationPath, $imageData) === false) {
            echo "Error: Unable to save the image.";
        }
    }

}
