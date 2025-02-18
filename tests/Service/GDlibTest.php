<?php

namespace Test\Service;

use ImageResizer\Service\GDlib;
use ImageResizer\Service\LoggerService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Test\Dto\ImageDimensionsDTO;
use Test\Dto\ResizedImageDimensions;

class GDlibTest extends TestCase
{
    private string $cacheDirectory;
    private mixed $mockLogger;

    public function setUp(): void
    {
        // Ignore logs
        $this->mockLogger = $this->createMock(LoggerService::class);
        $this->mockLogger->expects($this->any())
            ->method('info')
            ->willReturnCallback(function () {
                // Simulates the logging behavior but does nothing.
            });
        ;

        $this->cacheDirectory = getenv('CACHE_DIR');
        // Ensure the cache directory is clean before each test
        $filesystem = new Filesystem();
        if ($filesystem->exists($this->cacheDirectory)) {
            $filesystem->remove($this->cacheDirectory);
        }
        $filesystem->mkdir($this->cacheDirectory);
    }

    /**
     * @dataProvider imageProvider
    */
    public function testLoadImageStrategyGdImageAndOnlyAllowedExtensions(string $image, bool $shouldThrowException, mixed $expectedType)
    {
        $gdLib = new GDlib($this->mockLogger);

        // Use ReflectionMethod to access the private method
        $reflection = new \ReflectionMethod(GDlib::class, 'loadImageStrategy');
        $reflection->setAccessible(true); // Allow access to the private method

        // Test data
        $imagesPath = getenv('ASSETS_PATH') . "archive/";
        $sourcePath = $imagesPath . $image;

        if ($shouldThrowException) {
            $this->expectException(\InvalidArgumentException::class);
        }

        // Call the private method using reflection and assert the result
        $result = $reflection->invoke($gdLib, $sourcePath);

        // Assert that the result is an image resource
        $this->assertInstanceOf($expectedType, $result, "Expected an instance of $expectedType.");
    }

    private function imageProvider(): array
    {
        return [
            ["Dark_Side_of_the_Moon.png", false, \GdImage::class],
            ["Dark_Side_of_the_Moon.jpg", false, \GdImage::class],
            ["sample.gif", false, \GdImage::class],
            ["example.webp", true, null],
        ];
    }

    public function testSaveImageStrategy()
    {
        $gdLib = new GDlib($this->mockLogger);
        // Use ReflectionMethod to access the private method
        $saveImageStrategyReflection = new \ReflectionMethod(GDlib::class, 'saveImageStrategy');
        $saveImageStrategyReflection->setAccessible(true); // Allow access to the private method

        $imagesPath = getenv('ASSETS_PATH') . "archive/";
        $cacheFolder = $this->cacheDirectory . "/";
        $sourcePath = $imagesPath . "Dark_Side_of_the_Moon.jpg";
        $expectedCacheKey = md5($sourcePath) . ".jpg";
        $size = "thumbnail";
        $image = \imagecreatefromjpeg($sourcePath);
        $pathWithSize = $saveImageStrategyReflection->invoke($gdLib, $image, $sourcePath, $cacheFolder, $size, $expectedCacheKey);
        $cachedImage = file_exists($pathWithSize);
        $this->assertTrue($cachedImage, 'Expected the cached image file to exist at ' . $pathWithSize);
    }

    public function testResizeImageResource()
    {
        $gdLib = new GDlib($this->mockLogger);
        // Use ReflectionMethod to access the private method
        $resizeImageResourceReflection = new \ReflectionMethod(GDlib::class, 'resizeImageResource');
        $resizeImageResourceReflection->setAccessible(true);
        $imagesPath = getenv('ASSETS_PATH') . "archive/";
        $sourcePath = $imagesPath . "Dark_Side_of_the_Moon.jpg";
        $image = \imagecreatefromjpeg($sourcePath);
        $srcX = 0;
        $srcY = 0;
        $cropWidth = 700;
        $cropHeight = 700;
        $newWidth = 300;
        $newHeight = 300;
        $image = $resizeImageResourceReflection->invoke($gdLib, $image, $srcX, $srcY, $cropWidth, $cropHeight, $newWidth, $newHeight);
        $this->assertEquals($newWidth, imagesx($image));
        $this->assertEquals($newHeight, imagesy($image));
        $this->assertTrue(imageistruecolor($image));
        $this->assertInstanceOf(\GdImage::class, $image);
    }

    /**
     * @dataProvider dimensionsProvider
    */
    public function testcalculateNewDimensions(ImageDimensionsDTO $params, ResizedImageDimensions $expectetResults)
    {
        $gdLib = new GDlib($this->mockLogger);
        // Use ReflectionMethod to access the private method
        $calculateNewDimensionsReflection = new \ReflectionMethod(GDlib::class, 'calculateNewDimensions');
        $calculateNewDimensionsReflection->setAccessible(true);

        [$cropWidth, $cropHeight, $srcX, $srcY, $newWidth, $newHeight] = $calculateNewDimensionsReflection->invokeArgs(
            $gdLib,
            [$params->originalWidth, $params->originalHeight, $params->newWidth, $params->newHeight, $params->isCrop]
        );

        $this->assertSame($expectetResults->toArray(), [$cropWidth, $cropHeight, $newWidth, $newHeight, $srcX, $srcY]);
    }

    private function dimensionsProvider(): array
    {
        /**
         *  Caso 1:
         *     $originalWidth e $originalHeight sono > 0, quindi:
         *          - $newWidth e $newHeight restano identici
         *     $crop è false, quindi
         *          - $cropWidth === $originalWidth
         *          - $cropHeight === $originalHeight
         *          - $srcX e $srcY sono 0
         *
         *  Caso 2:
         *     ..
         *     $crop è true, quindi
         *          - $cropWidth e $cropHeight sono ricalcolati (il valore più basso viene associato ad entrambi)
         *          - $srcX e $srcY sono ricalcolati ($originalWidth è maggiore quindi $srcX è uguale a ($originalWidth - $originalHeight) / 2 )
         *
         *  Caso 3:
         *     ..
         *     $crop è true, quindi
         *          - $cropWidth e $cropHeight sono ricalcolati (il valore più basso viene associato ad entrambi)
         *          - $srcX e $srcY sono ricalcolati ($originalHeight è maggiore quindi $srcY è uguale a ($originalHeight - $originalWidth) / 2 )
         *
         *  Caso 4:
         *     $newWidth = 0, quindi:
         *          - assegna $crop = false
         *          - assegna $newHeight = $newWidth
         *
         *  Caso 5:
         *     $newHeight = 0, quindi:
         *          - assegna $crop = false
         *          - assegna $newWidth = $newHeight
         *
        */
        return [
            [
                new ImageDimensionsDTO(600, 800, 300, 300, false),
                new ResizedImageDimensions(600, 800, 300, 300, 0, 0)
            ], // Caso 1
            [
                new ImageDimensionsDTO(1500, 1400, 400, 400, true),
                new ResizedImageDimensions(1400, 1400, 400, 400, 50, 0)
            ], // Caso 2
            [
                new ImageDimensionsDTO(1400, 1600, 400, 400, true),
                new ResizedImageDimensions(1400, 1400, 400, 400, 0, 100)
            ], // Caso 3
            [
                new ImageDimensionsDTO(1600, 1600, 0, 500, true),
                new ResizedImageDimensions(1600, 1600, 500, 500, 0, 0)
            ], // Caso 4
            [
                new ImageDimensionsDTO(1600, 1600, 400, 0, true),
                new ResizedImageDimensions(1600, 1600, 400, 400, 0, 0)
            ], // Caso 5
        ];
    }

}
