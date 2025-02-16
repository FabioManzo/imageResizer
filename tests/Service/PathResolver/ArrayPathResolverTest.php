<?php

namespace Test\Service\PathResolver;

use ImageResizer\Service\PathResolver\ArrayPathResolver;
use PHPUnit\Framework\TestCase;

class ArrayPathResolverTest extends TestCase
{
    /**
     * @dataProvider pathProvider
     */
    public function testGet(string $path, mixed $expectedResult, bool $shouldThrowException)
    {
        $resolver = new ArrayPathResolver();
        $config = [
            "archive" => "archive/",
            "thumbnail" => [
                "width" => 400,
                "height" => 400,
                "crop" => false,
                "filters" => [
                    "FlipHorizontal",
                    "BlackAndWhite"
                ]
            ]
        ];

        if ($shouldThrowException) {
            $this->expectException(\InvalidArgumentException::class);
        }

        $result = $resolver->get($path, $config);
        $this->assertEquals($expectedResult, $result);
    }

    private function pathProvider(): array
    {
        return [
            ["archive", "archive/", false],
            ["thumbnail/width", 400, false],
            ["thumbnail/filters", ["FlipHorizontal", "BlackAndWhite"], false],
            ['nonexistent/path', null, true], // Invalid path, should throw an exception
        ];
    }
}