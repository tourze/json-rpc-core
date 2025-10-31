<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Exception\JsonRpcExceptionInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcInvalidParamsException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(JsonRpcInvalidParamsException::class)]
final class JsonRpcInvalidParamsExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCreation(): void
    {
        $violations = ['param1' => 'This field is required', 'param2' => 'Invalid value'];
        $exception = new JsonRpcInvalidParamsException($violations);

        $this->assertInstanceOf(JsonRpcExceptionInterface::class, $exception);
        $this->assertInstanceOf(JsonRpcException::class, $exception);
        $this->assertEquals(JsonRpcInvalidParamsException::CODE, $exception->getCode());
        $this->assertEquals(-32602, $exception->getCode());
        $this->assertEquals('Invalid params', $exception->getMessage());

        $errorData = $exception->getErrorData();
        $this->assertArrayHasKey(JsonRpcInvalidParamsException::DATA_VIOLATIONS_KEY, $errorData);
        $this->assertEquals($violations, $errorData[JsonRpcInvalidParamsException::DATA_VIOLATIONS_KEY]);
    }

    public function testExceptionWithEmptyViolations(): void
    {
        $violations = [];
        $exception = new JsonRpcInvalidParamsException($violations);

        $this->assertEquals(JsonRpcInvalidParamsException::CODE, $exception->getCode());
        $this->assertEquals('Invalid params', $exception->getMessage());

        $errorData = $exception->getErrorData();
        $this->assertArrayHasKey(JsonRpcInvalidParamsException::DATA_VIOLATIONS_KEY, $errorData);
        $this->assertEquals([], $errorData[JsonRpcInvalidParamsException::DATA_VIOLATIONS_KEY]);
    }

    public function testExceptionCode(): void
    {
        $exception = new JsonRpcInvalidParamsException([]);

        $this->assertEquals(-32602, $exception->getCode());
        $this->assertEquals(-32602, JsonRpcInvalidParamsException::CODE);
    }

    public function testDataViolationsKey(): void
    {
        $this->assertEquals('violations', JsonRpcInvalidParamsException::DATA_VIOLATIONS_KEY);
    }

    public function testExceptionInheritsFromJsonRpcException(): void
    {
        $exception = new JsonRpcInvalidParamsException([]);

        $this->assertInstanceOf(JsonRpcException::class, $exception);
        $this->assertEquals($exception->getErrorCode(), $exception->getCode());
        $this->assertEquals($exception->getErrorMessage(), $exception->getMessage());
    }
}
