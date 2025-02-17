<?php

namespace Test\Service;

use ImageResizer\Enum\ParserEnum;
use ImageResizer\Service\ConfigLoader;
use ImageResizer\Service\ParserService;
use PHPUnit\Framework\TestCase;

class ConfigLoaderTest extends TestCase
{
    public function testConfigLoading()
    {
        $parserService = new ParserService();
        $parser = $parserService->getParser(ParserEnum::SimpleXmlParser->name);
        $configLoader = new ConfigLoader($parser, 'myConfig.xml');
        $config = $configLoader->getConfig();

        // Assert that all keys, from all the xml files, are present
        $this->assertArrayHasKey('archive', $config);
        $this->assertArrayHasKey('imageCache', $config);
        $this->assertArrayHasKey('mode', $config);
        $this->assertArrayHasKey('jpg_compression', $config);
        $this->assertArrayHasKey('thumbnail', $config);
        $this->assertArrayHasKey('medium', $config);
        $this->assertArrayHasKey('full', $config);
        $this->assertArrayHasKey('arrayvalue', $config);
        $this->assertArrayHasKey('group', $config);
        $this->assertArrayHasKey('longtext', $config);
        $this->assertArrayHasKey('value1', $config);
        $this->assertArrayHasKey('value2', $config);
        $this->assertArrayHasKey('value3', $config);

        // Assert that the latest value of the same property has been set
        $this->assertEquals($config['value1'], "prova3");

        // Assert nested properties are correctly handled
        $this->assertIsArray($config['thumbnail']);
        $this->assertIsArray($config['thumbnail']['filters']);
    }
}