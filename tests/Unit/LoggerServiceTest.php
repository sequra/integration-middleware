<?php

namespace SeQura\Middleware\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SeQura\Middleware\Service\Infrastructure\LoggerService;

/**
 * Class LoggerServiceTest
 *
 * @package SeQura\Middleware\Tests\Unit
 */
class LoggerServiceTest extends TestCase
{
    private \ReflectionMethod $formatContextValue;
    private LoggerService $loggerService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loggerService = LoggerService::getInstance();

        $this->formatContextValue = new \ReflectionMethod(LoggerService::class, 'formatContextValue');
        $this->formatContextValue->setAccessible(true);
    }

    public function testThrowableIsFormattedWithoutPrintR(): void
    {
        $exception = new \RuntimeException('Something went wrong');

        $result = $this->formatContextValue->invoke($this->loggerService, $exception);

        $this->assertStringContainsString('RuntimeException: Something went wrong', $result);
        $this->assertStringContainsString(__FILE__, $result);
        // Must NOT contain full stack trace output from print_r
        $this->assertStringNotContainsString('#0 ', $result);
        $this->assertStringNotContainsString('Array', $result);
    }

    public function testNestedExceptionOnlyShowsOuterException(): void
    {
        $previous = new \InvalidArgumentException('Root cause');
        $exception = new \RuntimeException('Wrapper', 0, $previous);

        $result = $this->formatContextValue->invoke($this->loggerService, $exception);

        $this->assertStringContainsString('RuntimeException: Wrapper', $result);
        // Should NOT recurse into the previous exception
        $this->assertStringNotContainsString('Root cause', $result);
        $this->assertStringNotContainsString('#0 ', $result);
    }

    public function testObjectUsesJsonEncode(): void
    {
        $object = new \stdClass();
        $object->key = 'value';
        $object->number = 42;

        $result = $this->formatContextValue->invoke($this->loggerService, $object);

        $this->assertStringContainsString('"key":"value"', $result);
        $this->assertStringContainsString('"number":42', $result);
    }

    public function testNonSerializableObjectFallsBackToClassName(): void
    {
        $object = new class {
            public float $value = NAN;
        };

        $result = $this->formatContextValue->invoke($this->loggerService, $object);

        // JSON_PARTIAL_OUTPUT_ON_ERROR will produce output or fall back to class name
        $this->assertNotEmpty($result);
        // Should not crash or produce unbounded output
        $this->assertLessThan(1000, strlen($result));
    }

    public function testScalarStringIsReturnedAsIs(): void
    {
        $result = $this->formatContextValue->invoke($this->loggerService, 'simple string');

        $this->assertEquals('simple string', $result);
    }

    public function testIntegerIsReturnedViaPrintR(): void
    {
        $result = $this->formatContextValue->invoke($this->loggerService, 42);

        $this->assertEquals('42', $result);
    }

    public function testArrayIsReturnedViaPrintR(): void
    {
        $result = $this->formatContextValue->invoke($this->loggerService, ['a', 'b', 'c']);

        $this->assertStringContainsString('a', $result);
        $this->assertStringContainsString('b', $result);
        $this->assertStringContainsString('c', $result);
    }

    public function testNullIsHandled(): void
    {
        $result = $this->formatContextValue->invoke($this->loggerService, null);

        $this->assertIsString($result);
    }
}
