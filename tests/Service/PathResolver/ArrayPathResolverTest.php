<?php

namespace Test\Service\PathResolver;

use ImageResizer\Service\PathResolver\ArrayPathResolver;
use PHPUnit\Framework\TestCase;
use Test\Enum\AssertTypeEnum;

class ArrayPathResolverTest extends TestCase
{
    /**
     * @dataProvider pathProvider
     */
    public function testGet(string $path, mixed $expectedResult, bool $shouldThrowException, ?AssertTypeEnum $assertType = null)
    {
        $resolver = new ArrayPathResolver();
        $config = [
            "archive" => "archive/",
            "imageCache" => "cache/",
            "mode" => "GDLIB",
            "jpg_compression" => 90,
            "thumbnail" => [
                "width" => 400,
                "height" => 400
            ],
            "medium" => [
                "width" => 400,
                "height" => 400,
                "crop" => true
            ],
            "full" => [
                "width" => 800,
                "height" => 600,
                "crop" => false
            ],
            "arrayvalue" => [
                0 => "abc",
                1 => "def"
            ],
            "group" => [
                "innergroup" => [
                    "value1" => "abc",
                    "value2" => "def"
                ],
            ],
            "longtext" => "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Magna laus. Cave putes quicquam esse verius. Hoc etsi multimodis reprehendi potest, tamen accipio, quod dant. Duo Reges: constructio interrete. </p>",
            "value1" => "prova3",
            "value2" => "prova4",
            "value3" => "prova5"
        ];

        if ($shouldThrowException) {
            $this->expectException(\InvalidArgumentException::class);
        }

        $result = $resolver->get($path, $config);
        $this->assertEquals($expectedResult, $result);
        if ($assertType) {
            $this->assertType($result, $assertType);
        }
    }

    public function pathProvider()
    {
        return [
            ["archive", "archive/", false, AssertTypeEnum::String],
            ["imageCache", "cache/", false, AssertTypeEnum::String],
            ["mode", "GDLIB", false, AssertTypeEnum::String],
            ["jpg_compression", 90, false, AssertTypeEnum::Int],
            ["thumbnail/width", 400, false, AssertTypeEnum::Int],
            ["thumbnail/height", 400, false, AssertTypeEnum::Int],
            ["thumbnail/crop", false, false, AssertTypeEnum::Bool],
            ["thumbnail/filters", ["FlipHorizontal", "BlackAndWhite"], false, "array"],
            ["medium/width", 400, false, AssertTypeEnum::Int],
            ["medium/height", 400, false, AssertTypeEnum::Int],
            ["medium/crop", true, false, AssertTypeEnum::Bool],
            ["full/width", 800, false, AssertTypeEnum::Int],
            ["full/height", 600, false, AssertTypeEnum::Int],
            ["full/crop", false, false, AssertTypeEnum::Bool],
            ["arrayvalue", ["abc", "def"] , false, "array"],
            ["group/innergroup/value1", "abc", false, AssertTypeEnum::String],
            ["group/innergroup/value2", "def", false, AssertTypeEnum::String],
            ["longtext", "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Magna laus. Cave putes quicquam esse verius. Hoc etsi multimodis reprehendi potest, tamen accipio, quod dant. Duo Reges: constructio interrete. </p>", false, AssertTypeEnum::String],
            ["value1", "prova3", false, AssertTypeEnum::String],
            ["value2", "prova4", false, AssertTypeEnum::String],
            ["value3", "prova5", false, AssertTypeEnum::String],
            ['nonexistent/path', null, true], // Invalid path, should throw an exception
        ];
    }

    private function assertType(mixed $result, AssertTypeEnum $assertType): void
    {
        switch ($assertType->name) {
            case AssertTypeEnum::String->name:
                $this->assertIsString($result);
                break;
            case AssertTypeEnum::Int->name:
                $this->assertIsInt($result);
                break;
            case AssertTypeEnum::Bool->name:
                $this->assertIsBool($result);
                break;
            case AssertTypeEnum::Array->name:
                $this->assertIsArray($result);
                break;
            default:
                throw new \InvalidArgumentException("Unsupported type assertion: " . $assertType->name);
        }
    }
}