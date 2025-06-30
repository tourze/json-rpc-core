<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Exception\JsonRpcExceptionInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcRuntimeException;

class JsonRpcRuntimeExceptionTest extends TestCase
{
    public function testExceptionCreation(): void
    {
        $message = 'Runtime error occurred';
        $data = ['error_type' => 'execution_failure', 'timestamp' => time()];

        $exception = new JsonRpcRuntimeException($message, $data);

        $this->assertInstanceOf(JsonRpcExceptionInterface::class, $exception);
        $this->assertInstanceOf(JsonRpcException::class, $exception);
        $this->assertEquals(JsonRpcRuntimeException::CODE, $exception->getCode());
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($data, $exception->getErrorData());
    }

    public function testExceptionWithoutData(): void
    {
        $message = 'Runtime error occurred';

        $exception = new JsonRpcRuntimeException($message);

        $this->assertEquals(JsonRpcRuntimeException::CODE, $exception->getCode());
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals([], $exception->getErrorData());
    }

    public function testExceptionCode(): void
    {
        $exception = new JsonRpcRuntimeException('Test message');

        $this->assertEquals(-32603, $exception->getCode());
        $this->assertEquals(-32603, JsonRpcRuntimeException::CODE);
    }

    public function testExceptionInheritsFromJsonRpcException(): void
    {
        $exception = new JsonRpcRuntimeException('Test message');

        $this->assertInstanceOf(JsonRpcException::class, $exception);
        $this->assertEquals($exception->getErrorCode(), $exception->getCode());
        $this->assertEquals($exception->getErrorMessage(), $exception->getMessage());
    }
}