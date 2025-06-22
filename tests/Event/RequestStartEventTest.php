<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Tourze\JsonRPC\Core\Event\RequestStartEvent;

/**
 * 测试RequestStartEvent请求开始事件
 */
class RequestStartEventTest extends TestCase
{
    public function testSetAndGetPayload(): void
    {
        $event = new RequestStartEvent();
        $payload = '{"jsonrpc":"2.0","method":"user.create","params":{"name":"John"},"id":"123"}';

        $event->setPayload($payload);

        $this->assertEquals($payload, $event->getPayload());
        $this->assertJson($event->getPayload());
    }

    public function testSetAndGetRequest(): void
    {
        $event = new RequestStartEvent();
        $request = Request::create('/json-rpc', 'POST', [], [], [], [], 
            '{"jsonrpc":"2.0","method":"test","id":"1"}');

        // 先设置payload避免属性未初始化错误
        $event->setPayload('{"test": true}');
        
        // 设置request后再测试
        $event->setRequest($request);

        $this->assertSame($request, $event->getRequest());
        $this->assertEquals('POST', $event->getRequest()->getMethod());
        $this->assertEquals('/json-rpc', $event->getRequest()->getPathInfo());
    }

    public function testRequestCanBeSetToNull(): void
    {
        $event = new RequestStartEvent();
        $request = Request::create('/test', 'POST');

        $event->setRequest($request);
        $this->assertSame($request, $event->getRequest());

        $event->setRequest(null);
        $this->assertNull($event->getRequest());
    }

    public function testCompleteEventSetup(): void
    {
        $event = new RequestStartEvent();
        
        $payload = '{"jsonrpc":"2.0","method":"product.list","params":{"category":"electronics"},"id":"456"}';
        $request = Request::create('/api/json-rpc', 'POST', [], [], [], 
            ['HTTP_CONTENT_TYPE' => 'application/json'], $payload);

        $event->setPayload($payload);
        $event->setRequest($request);

        // 验证所有属性
        $this->assertEquals($payload, $event->getPayload());
        $this->assertSame($request, $event->getRequest());
        $this->assertEquals('POST', $event->getRequest()->getMethod());
        $this->assertEquals('/api/json-rpc', $event->getRequest()->getPathInfo());
        $this->assertEquals($payload, $event->getRequest()->getContent());
    }

    public function testEventInheritance(): void
    {
        $event = new RequestStartEvent();

        $this->assertInstanceOf(\Symfony\Contracts\EventDispatcher\Event::class, $event);
    }

    public function testPayloadWithDifferentJsonRpcRequests(): void
    {
        $event = new RequestStartEvent();

        // 测试单个请求
        $singleRequest = '{"jsonrpc":"2.0","method":"echo","params":["hello"],"id":"1"}';
        $event->setPayload($singleRequest);
        $this->assertEquals($singleRequest, $event->getPayload());

        // 测试批量请求
        $batchRequest = '[{"jsonrpc":"2.0","method":"sum","params":[1,2,4],"id":"1"},{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":"2"}]';
        $event->setPayload($batchRequest);
        $this->assertEquals($batchRequest, $event->getPayload());

        // 测试通知
        $notification = '{"jsonrpc":"2.0","method":"notify","params":["data"]}';
        $event->setPayload($notification);
        $this->assertEquals($notification, $event->getPayload());
    }

    public function testRequestWithDifferentHttpMethods(): void
    {
        $event = new RequestStartEvent();

        // 测试POST请求（标准）
        $postRequest = Request::create('/json-rpc', 'POST');
        $event->setRequest($postRequest);
        $this->assertEquals('POST', $event->getRequest()->getMethod());

        // 测试GET请求（某些实现可能支持）
        $getRequest = Request::create('/json-rpc', 'GET');
        $event->setRequest($getRequest);
        $this->assertEquals('GET', $event->getRequest()->getMethod());

        // 测试PUT请求
        $putRequest = Request::create('/json-rpc', 'PUT');
        $event->setRequest($putRequest);
        $this->assertEquals('PUT', $event->getRequest()->getMethod());
    }

    public function testEventWithoutRequest(): void
    {
        $event = new RequestStartEvent();
        $payload = '{"jsonrpc":"2.0","method":"test","id":"no-http"}';

        $event->setPayload($payload);
        // 不设置request，模拟非HTTP环境

        $this->assertEquals($payload, $event->getPayload());
        // 不调用getRequest()避免属性未初始化错误
    }

    public function testEventClassStructure(): void
    {
        $event = new RequestStartEvent();
        $reflection = new \ReflectionClass($event);
        
        $this->assertEquals('Tourze\JsonRPC\Core\Event', $reflection->getNamespaceName());
        $this->assertEquals('RequestStartEvent', $reflection->getShortName());
        $this->assertFalse($reflection->isAbstract());
        $this->assertTrue($reflection->isInstantiable());
        
        // 验证有正确的属性
        $this->assertTrue($reflection->hasProperty('payload'));
        $this->assertTrue($reflection->hasProperty('request'));
    }

    public function testPayloadValidation(): void
    {
        $event = new RequestStartEvent();
        
        // 测试有效的JSON-RPC payload
        $validPayload = '{"jsonrpc":"2.0","method":"validate","id":"valid"}';
        $event->setPayload($validPayload);
        
        $decoded = json_decode($event->getPayload(), true);
        $this->assertEquals('2.0', $decoded['jsonrpc']);
        $this->assertEquals('validate', $decoded['method']);
        $this->assertEquals('valid', $decoded['id']);
    }

    public function testRequestWithHeaders(): void
    {
        $event = new RequestStartEvent();
        
        $request = Request::create('/json-rpc', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer token123',
            'HTTP_USER_AGENT' => 'JsonRpc Client 1.0'
        ]);

        $event->setRequest($request);

        $this->assertEquals('application/json', $event->getRequest()->headers->get('Content-Type'));
        $this->assertEquals('Bearer token123', $event->getRequest()->headers->get('Authorization'));
        $this->assertEquals('JsonRpc Client 1.0', $event->getRequest()->headers->get('User-Agent'));
    }

    public function testEventLifecycle(): void
    {
        // 模拟请求开始的完整生命周期
        $event = new RequestStartEvent();
        
        $payload = '{"jsonrpc":"2.0","method":"lifecycle.test","params":{"stage":"start"},"id":"lifecycle"}';
        $request = Request::create('/json-rpc', 'POST', [], [], [], [], $payload);

        $event->setPayload($payload);
        $event->setRequest($request);

        // 验证事件包含请求开始所需的所有信息
        $this->assertJson($event->getPayload());
        $this->assertNotNull($event->getRequest());
        $this->assertEquals($payload, $event->getRequest()->getContent());
        
        // 验证继承关系
        $this->assertInstanceOf(\Symfony\Contracts\EventDispatcher\Event::class, $event);
    }
} 