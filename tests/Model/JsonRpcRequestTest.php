<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Model;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

class JsonRpcRequestTest extends TestCase
{
    public function testDefaultJsonRpcVersion(): void
    {
        $request = new JsonRpcRequest();
        $this->assertEquals('2.0', $request->getJsonrpc());
    }

    public function testSetGetJsonrpc(): void
    {
        $request = new JsonRpcRequest();
        $request->setJsonrpc('2.0');
        $this->assertEquals('2.0', $request->getJsonrpc());
    }

    public function testSetGetId(): void
    {
        $request = new JsonRpcRequest();

        // 测试整数 ID
        $request->setId(123);
        $this->assertEquals(123, $request->getId());

        // 测试字符串 ID
        $request->setId('test-id');
        $this->assertEquals('test-id', $request->getId());
    }

    public function testSetInvalidId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Id must be either an int or a string');

        $request = new JsonRpcRequest();
        $request->setId(['invalid']);
    }

    public function testIsNotification(): void
    {
        $request = new JsonRpcRequest();

        // 默认情况下，ID 为 null，应该是通知
        $this->assertTrue($request->isNotification());

        // 设置 ID 后，不再是通知
        $request->setId(123);
        $this->assertFalse($request->isNotification());
    }

    public function testSetGetMethod(): void
    {
        $request = new JsonRpcRequest();
        $method = 'test.method';

        $request->setMethod($method);
        $this->assertEquals($method, $request->getMethod());
    }

    public function testSetGetParams(): void
    {
        $request = new JsonRpcRequest();
        $params = new JsonRpcParams();
        $params->add(['key' => 'value']);

        $request->setParams($params);
        $this->assertSame($params, $request->getParams());
        $this->assertEquals(['key' => 'value'], $request->getParams()->all());
    }
}
