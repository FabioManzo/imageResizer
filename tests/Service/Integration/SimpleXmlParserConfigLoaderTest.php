<?php

namespace Test\Service\Integration;

use ImageResizer\Enum\ParserEnum;
use ImageResizer\Service\ConfigLoader;
use ImageResizer\Service\ParserService;
use PHPUnit\Framework\TestCase;
use Test\Enum\AssertTypeEnum;

class SimpleXmlParserConfigLoaderTest extends TestCase
{
    /**
     * @dataProvider pathProvider
     */
    public function testIntegration(string $path, mixed $expectedResult, AssertTypeEnum $assertType)
    {
        $parserService = new ParserService();
        $parser = $parserService->getParser(ParserEnum::SimpleXmlParser->name);
        $configLoader = new ConfigLoader("myConfig.xml", $parser);
        $result = $configLoader->get($path);
        $this->assertEquals($expectedResult, $result);
        $this->assertType($result, $assertType);
    }

    private function pathProvider(): array
    {
        return [
            ["archive", "archive/", AssertTypeEnum::String],
            ["imageCache", "cache/", AssertTypeEnum::String],
            ["mode", "GDLIB", AssertTypeEnum::String],
            ["jpg_compression", 90, AssertTypeEnum::Int],
            ["thumbnail/width", 400, AssertTypeEnum::Int],
            ["thumbnail/height", 400, AssertTypeEnum::Int],
            ["thumbnail/crop", false, AssertTypeEnum::Bool],
            ["thumbnail/filters", ["FlipHorizontal", "BlackAndWhite"], AssertTypeEnum::Array],
            ["medium/width", 400, AssertTypeEnum::Int],
            ["medium/height", 400, AssertTypeEnum::Int],
            ["medium/crop", true, AssertTypeEnum::Bool],
            ["full/width", 800, AssertTypeEnum::Int],
            ["full/height", 600, AssertTypeEnum::Int],
            ["full/crop", false, AssertTypeEnum::Bool],
            ["arrayvalue", ["abc", "def"], AssertTypeEnum::Array],
            ["group/innergroup/value1", "abc", AssertTypeEnum::String],
            ["group/innergroup/value2", "def", AssertTypeEnum::String],
            ["longtext", "<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Magna laus. Cave putes quicquam esse verius. Hoc etsi multimodis reprehendi potest, tamen accipio, quod dant. Duo Reges: constructio interrete. </p>", AssertTypeEnum::String],
            ["value1", "prova3", AssertTypeEnum::String],
            ["value2", "prova4", AssertTypeEnum::String],
            ["value3", "prova5", AssertTypeEnum::String],
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
