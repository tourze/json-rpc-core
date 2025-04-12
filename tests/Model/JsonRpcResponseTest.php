<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Model;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;

class JsonRpcResponseTest extends TestCase
{
    public function testDefaultJsonRpcVersion(): void
    {
        $response = new JsonRpcResponse();
        $this->assertEquals('2.0', $response->getJsonrpc());
    }

    public function testSetGetJsonrpc(): void
    {
        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $this->assertEquals('2.0', $response->getJsonrpc());
    }

    public function testSetGetId(): void
    {
        $response = new JsonRpcResponse();

        // 测试整数 ID
        $response->setId(123);
        $this->assertEquals(123, $response->getId());

        // 测试字符串 ID
        $response->setId('test-id');
        $this->assertEquals('test-id', $response->getId());
    }

    public function testSetInvalidId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Id must be either an int or a string');

        $response = new JsonRpcResponse();
        $response->setId(['invalid']);
    }

    public function testSetGetResult(): void
    {
        $response = new JsonRpcResponse();
        $result = ['data' => 'test'];

        $response->setResult($result);
        $this->assertEquals($result, $response->getResult());
    }

    public function testSetGetError(): void
    {
        $response = new JsonRpcResponse();
        $error = new JsonRpcException(100, 'Test error', ['detail' => 'Additional info']);

        $response->setError($error);
        $this->assertSame($error, $response->getError());
        $this->assertEquals(100, $response->getError()->getErrorCode());
        $this->assertEquals('Test error', $response->getError()->getErrorMessage());
        $this->assertEquals(['detail' => 'Additional info'], $response->getError()->getErrorData());
    }

    public function testSetIsNotification(): void
    {
        $response = new JsonRpcResponse();

        // 默认情况下不是通知
        $this->assertFalse($response->isNotification());

        // 设置为通知
        $response->setIsNotification(true);
        $this->assertTrue($response->isNotification());
    }
}
