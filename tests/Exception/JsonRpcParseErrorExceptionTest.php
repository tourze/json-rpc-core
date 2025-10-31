<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Exception\JsonRpcExceptionInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcParseErrorException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(JsonRpcParseErrorException::class)]
final class JsonRpcParseErrorExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCreation(): void
    {
        $content = '{"invalid": json}';
        $parseErrorCode = JSON_ERROR_SYNTAX;
        $parseErrorMessage = 'Syntax error';

        $exception = new JsonRpcParseErrorException($content, $parseErrorCode, $parseErrorMessage);

        $this->assertInstanceOf(JsonRpcExceptionInterface::class, $exception);
        $this->assertInstanceOf(JsonRpcException::class, $exception);
        $this->assertEquals(JsonRpcParseErrorException::CODE, $exception->getCode());
        $this->assertEquals(-32700, $exception->getCode());
        $this->assertEquals('Parse error', $exception->getMessage());
        $this->assertEquals($content, $exception->getContent());
        $this->assertEquals($parseErrorCode, $exception->getParseErrorCode());
        $this->assertEquals($parseErrorMessage, $exception->getParseErrorMessage());
    }

    public function testExceptionCreationWithContentOnly(): void
    {
        $content = '{"invalid": json}';

        $exception = new JsonRpcParseErrorException($content);

        $this->assertEquals(JsonRpcParseErrorException::CODE, $exception->getCode());
        $this->assertEquals('Parse error', $exception->getMessage());
        $this->assertEquals($content, $exception->getContent());
        $this->assertNull($exception->getParseErrorCode());
        $this->assertEquals('', $exception->getParseErrorMessage());
    }

    public function testExceptionCreationWithContentAndCode(): void
    {
        $content = '{"invalid": json}';
        $parseErrorCode = JSON_ERROR_SYNTAX;

        $exception = new JsonRpcParseErrorException($content, $parseErrorCode);

        $this->assertEquals($content, $exception->getContent());
        $this->assertEquals($parseErrorCode, $exception->getParseErrorCode());
        $this->assertEquals('', $exception->getParseErrorMessage());
    }

    public function testExceptionCode(): void
    {
        $exception = new JsonRpcParseErrorException('test');

        $this->assertEquals(-32700, $exception->getCode());
        $this->assertEquals(-32700, JsonRpcParseErrorException::CODE);
    }

    public function testExceptionInheritsFromJsonRpcException(): void
    {
        $exception = new JsonRpcParseErrorException('test');

        $this->assertInstanceOf(JsonRpcException::class, $exception);
        $this->assertEquals($exception->getErrorCode(), $exception->getCode());
        $this->assertEquals($exception->getErrorMessage(), $exception->getMessage());
    }

    public function testGettersWork(): void
    {
        $content = ['invalid' => 'data'];
        $parseErrorCode = JSON_ERROR_DEPTH;
        $parseErrorMessage = 'Maximum stack depth exceeded';

        $exception = new JsonRpcParseErrorException($content, $parseErrorCode, $parseErrorMessage);

        $this->assertEquals($content, $exception->getContent());
        $this->assertEquals($parseErrorCode, $exception->getParseErrorCode());
        $this->assertEquals($parseErrorMessage, $exception->getParseErrorMessage());
    }
}
