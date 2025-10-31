<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\JsonRPC\Core\Exception\JsonRpcArgumentException;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Exception\JsonRpcExceptionInterface;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(JsonRpcArgumentException::class)]
final class JsonRpcArgumentExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCreation(): void
    {
        $message = 'Invalid argument provided';
        $data = ['parameter' => 'value'];

        $exception = new JsonRpcArgumentException($message, $data);

        $this->assertInstanceOf(JsonRpcExceptionInterface::class, $exception);
        $this->assertInstanceOf(JsonRpcException::class, $exception);
        $this->assertEquals(JsonRpcArgumentException::CODE, $exception->getCode());
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($data, $exception->getErrorData());
    }

    public function testExceptionWithoutData(): void
    {
        $message = 'Invalid argument provided';

        $exception = new JsonRpcArgumentException($message);

        $this->assertEquals(JsonRpcArgumentException::CODE, $exception->getCode());
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals([], $exception->getErrorData());
    }

    public function testExceptionCode(): void
    {
        $exception = new JsonRpcArgumentException('Test message');

        $this->assertEquals(-32602, $exception->getCode());
        $this->assertEquals(-32602, JsonRpcArgumentException::CODE);
    }

    public function testExceptionInheritsFromJsonRpcException(): void
    {
        $exception = new JsonRpcArgumentException('Test message');

        $this->assertInstanceOf(JsonRpcException::class, $exception);
        $this->assertEquals($exception->getErrorCode(), $exception->getCode());
        $this->assertEquals($exception->getErrorMessage(), $exception->getMessage());
    }
}
