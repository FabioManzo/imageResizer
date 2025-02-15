<?php

namespace Test\Service\Processor;

use PHPUnit\Framework\TestCase;
use ImageResizer\Service\Processor\ParamProcessor;

class ParamProcessorTest extends TestCase
{
    public function testNormalizeParamValue(): void
    {
        $processor = new ParamProcessor();

        // Reflection to access the private method
        $reflection = new \ReflectionClass($processor);
        $method = $reflection->getMethod('normalizeParamValue');
        $method->setAccessible(true); // Allows calling private methods

        // Test cases
        $this->assertSame(400, $method->invoke($processor, "400"));   // Int conversion
        $this->assertSame(3.14, $method->invoke($processor, "3.14")); // Float conversion
        $this->assertSame(true, $method->invoke($processor, "true")); // Boolean conversion
        $this->assertSame(false, $method->invoke($processor, "false"));// Boolean conversion
        $this->assertSame("hello", $method->invoke($processor, "hello")); // String remains string
    }
}
