<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Exception\JsonRpcExceptionInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcInvalidRequestException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(JsonRpcInvalidRequestException::class)]
final class JsonRpcInvalidRequestExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCreationWithContent(): void
    {
        $content = '{"invalid": "json"}';
        $description = 'Missing required field';

        $exception = new JsonRpcInvalidRequestException($content, $description);

        $this->assertInstanceOf(JsonRpcExceptionInterface::class, $exception);
        $this->assertInstanceOf(JsonRpcException::class, $exception);
        $this->assertEquals(JsonRpcInvalidRequestException::CODE, $exception->getCode());
        $this->assertEquals(-32600, $exception->getCode());
        $this->assertEquals("Invalid request: {$description}", $exception->getMessage());
        $this->assertEquals($content, $exception->getContent());
        $this->assertEquals($description, $exception->getDescription());
    }

    public function testExceptionCreationWithoutDescription(): void
    {
        $content = '{"invalid": "json"}';

        $exception = new JsonRpcInvalidRequestException($content);

        $this->assertEquals(JsonRpcInvalidRequestException::CODE, $exception->getCode());
        $this->assertEquals('Invalid request: ', $exception->getMessage());
        $this->assertEquals($content, $exception->getContent());
        $this->assertEquals('', $exception->getDescription());
    }

    public function testExceptionCode(): void
    {
        $exception = new JsonRpcInvalidRequestException('test');

        $this->assertEquals(-32600, $exception->getCode());
        $this->assertEquals(-32600, JsonRpcInvalidRequestException::CODE);
    }

    public function testExceptionInheritsFromJsonRpcException(): void
    {
        $exception = new JsonRpcInvalidRequestException('test');

        $this->assertInstanceOf(JsonRpcException::class, $exception);
        $this->assertEquals($exception->getErrorCode(), $exception->getCode());
        $this->assertEquals($exception->getErrorMessage(), $exception->getMessage());
    }

    public function testGettersWork(): void
    {
        $content = ['test' => 'data'];
        $description = 'Test description';

        $exception = new JsonRpcInvalidRequestException($content, $description);

        $this->assertEquals($content, $exception->getContent());
        $this->assertEquals($description, $exception->getDescription());
    }
}
