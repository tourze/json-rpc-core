<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Exception\JsonRpcExceptionInterface;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(JsonRpcException::class)]
final class JsonRpcExceptionTest extends AbstractExceptionTestCase
{
    public function testExceptionCreation(): void
    {
        $code = 100;
        $message = 'Test error message';
        $data = ['detail' => 'Some additional info'];

        $exception = new TestJsonRpcException($code, $message, $data);

        $this->assertInstanceOf(JsonRpcExceptionInterface::class, $exception);
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($data, $exception->getErrorData());
    }

    public function testExceptionWithPrevious(): void
    {
        $previousException = new \RuntimeException('Previous error');
        $exception = new TestJsonRpcException(100, 'Test error', [], $previousException);

        $this->assertSame($previousException, $exception->getPrevious());
    }

    public function testErrorCodeAndMessage(): void
    {
        $code = -32600;
        $message = 'Invalid Request';

        $exception = new TestJsonRpcException($code, $message);

        $this->assertEquals($code, $exception->getErrorCode());
        $this->assertEquals($message, $exception->getErrorMessage());
    }

    public function testSetErrorData(): void
    {
        $initialData = ['initial' => 'data'];
        $newData = ['new' => 'data'];

        $exception = new TestJsonRpcException(100, 'Test', $initialData);

        $this->assertEquals($initialData, $exception->getErrorData());

        $exception->setErrorData($newData);

        $this->assertEquals($newData, $exception->getErrorData());
    }

    public function testEmptyErrorData(): void
    {
        $exception = new TestJsonRpcException(100, 'Test');

        $this->assertEquals([], $exception->getErrorData());
    }
}
