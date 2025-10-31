<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Exception\JsonRpcExceptionInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcInternalErrorException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(JsonRpcInternalErrorException::class)]
final class JsonRpcInternalErrorExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCreationWithoutPrevious(): void
    {
        $exception = new JsonRpcInternalErrorException();

        $this->assertInstanceOf(JsonRpcExceptionInterface::class, $exception);
        $this->assertInstanceOf(JsonRpcException::class, $exception);
        $this->assertEquals(JsonRpcInternalErrorException::CODE, $exception->getCode());
        $this->assertEquals(-32603, $exception->getCode());
        $this->assertEquals([], $exception->getErrorData());
    }

    public function testExceptionCreationWithPrevious(): void
    {
        $previousException = new \RuntimeException('Previous error message');
        $exception = new JsonRpcInternalErrorException($previousException);

        $this->assertEquals(JsonRpcInternalErrorException::CODE, $exception->getCode());
        $this->assertArrayHasKey(JsonRpcInternalErrorException::DATA_PREVIOUS_KEY, $exception->getErrorData());

        $errorData = $exception->getErrorData();
        $this->assertIsString($errorData[JsonRpcInternalErrorException::DATA_PREVIOUS_KEY]);
        $this->assertStringContainsString('Previous error message', $errorData[JsonRpcInternalErrorException::DATA_PREVIOUS_KEY]);
    }

    public function testExceptionCode(): void
    {
        $exception = new JsonRpcInternalErrorException();

        $this->assertEquals(-32603, $exception->getCode());
        $this->assertEquals(-32603, JsonRpcInternalErrorException::CODE);
    }

    public function testExceptionInheritsFromJsonRpcException(): void
    {
        $exception = new JsonRpcInternalErrorException();

        $this->assertInstanceOf(JsonRpcException::class, $exception);
        $this->assertEquals($exception->getErrorCode(), $exception->getCode());
        $this->assertEquals($exception->getErrorMessage(), $exception->getMessage());
    }

    public function testDataPreviousKey(): void
    {
        $this->assertEquals('previous', JsonRpcInternalErrorException::DATA_PREVIOUS_KEY);
    }
}
