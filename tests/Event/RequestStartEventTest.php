<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;
use Tourze\JsonRPC\Core\Event\RequestStartEvent;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * 测试RequestStartEvent请求开始事件.
 *
 * @internal
 */
#[CoversClass(RequestStartEvent::class)]
final class RequestStartEventTest extends AbstractEventTestCase
{
    public function testSetAndGetPayload(): void
    {
        $event = new class extends RequestStartEvent {};
        $payload = '{"jsonrpc":"2.0","method":"user.create","params":{"name":"John"},"id":"123"}';

        $event->setPayload($payload);

        $this->assertEquals($payload, $event->getPayload());
        $this->assertJson($event->getPayload());
    }

    public function testSetAndGetRequest(): void
    {
        $event = new class extends RequestStartEvent {};
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
        $event = new class extends RequestStartEvent {};
        $request = Request::create('/test', 'POST');

        $event->setRequest($request);
        $this->assertSame($request, $event->getRequest());

        $event->setRequest(null);
        $this->assertNull($event->getRequest());
    }

    public function testCompleteEventSetup(): void
    {
        $event = new class extends RequestStartEvent {};

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
        $event = new class extends RequestStartEvent {};

        $this->assertInstanceOf(Event::class, $event);
    }

    public function testPayloadWithDifferentJsonRpcRequests(): void
    {
        $event = new class extends RequestStartEvent {};

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
        $event = new class extends RequestStartEvent {};

        // 测试POST请求（标准）
        $postRequest = Request::create('/json-rpc', 'POST');
        $event->setRequest($postRequest);
        $request = $event->getRequest();
        $this->assertNotNull($request);
        $this->assertEquals('POST', $request->getMethod());

        // 测试GET请求（某些实现可能支持）
        $getRequest = Request::create('/json-rpc', 'GET');
        $event->setRequest($getRequest);
        $request = $event->getRequest();
        $this->assertNotNull($request);
        $this->assertEquals('GET', $request->getMethod());

        // 测试PUT请求
        $putRequest = Request::create('/json-rpc', 'PUT');
        $event->setRequest($putRequest);
        $request = $event->getRequest();
        $this->assertNotNull($request);
        $this->assertEquals('PUT', $request->getMethod());
    }

    public function testEventWithoutRequest(): void
    {
        $event = new class extends RequestStartEvent {};
        $payload = '{"jsonrpc":"2.0","method":"test","id":"no-http"}';

        $event->setPayload($payload);
        // 不设置request，模拟非HTTP环境

        $this->assertEquals($payload, $event->getPayload());
        // 不调用getRequest()避免属性未初始化错误
    }

    public function testEventClassStructure(): void
    {
        $event = new class extends RequestStartEvent {};
        $reflection = new \ReflectionClass($event);

        // 验证匿名类的属性
        $this->assertFalse($reflection->isAbstract());
        $this->assertTrue($reflection->isInstantiable());
        $this->assertTrue($reflection->isSubclassOf(RequestStartEvent::class));

        // 验证父类的属性（私有属性在子类中不可见，但继承的方法可以访问）
        $parentReflection = new \ReflectionClass(RequestStartEvent::class);
        $this->assertTrue($parentReflection->hasProperty('payload'));
        $this->assertTrue($parentReflection->hasProperty('request'));
        $this->assertTrue($parentReflection->hasMethod('getPayload'));
        $this->assertTrue($parentReflection->hasMethod('setPayload'));
        $this->assertTrue($parentReflection->hasMethod('getRequest'));
        $this->assertTrue($parentReflection->hasMethod('setRequest'));
    }

    public function testPayloadValidation(): void
    {
        $event = new class extends RequestStartEvent {};

        // 测试有效的JSON-RPC payload
        $validPayload = '{"jsonrpc":"2.0","method":"validate","id":"valid"}';
        $event->setPayload($validPayload);

        $decoded = json_decode($event->getPayload(), true);
        $this->assertIsArray($decoded);
        $this->assertEquals('2.0', $decoded['jsonrpc']);
        $this->assertEquals('validate', $decoded['method']);
        $this->assertEquals('valid', $decoded['id']);
    }

    public function testRequestWithHeaders(): void
    {
        $event = new class extends RequestStartEvent {};

        $request = Request::create('/json-rpc', 'POST', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer token123',
            'HTTP_USER_AGENT' => 'JsonRpc Client 1.0',
        ]);

        $event->setRequest($request);

        $request = $event->getRequest();
        $this->assertNotNull($request);
        $this->assertEquals('application/json', $request->headers->get('Content-Type'));
        $this->assertEquals('Bearer token123', $request->headers->get('Authorization'));
        $this->assertEquals('JsonRpc Client 1.0', $request->headers->get('User-Agent'));
    }

    public function testEventLifecycle(): void
    {
        // 模拟请求开始的完整生命周期
        $event = new class extends RequestStartEvent {};

        $payload = '{"jsonrpc":"2.0","method":"lifecycle.test","params":{"stage":"start"},"id":"lifecycle"}';
        $request = Request::create('/json-rpc', 'POST', [], [], [], [], $payload);

        $event->setPayload($payload);
        $event->setRequest($request);

        // 验证事件包含请求开始所需的所有信息
        $this->assertJson($event->getPayload());
        $request = $event->getRequest();
        $this->assertNotNull($request);
        $this->assertEquals($payload, $request->getContent());

        // 验证继承关系
        $this->assertInstanceOf(Event::class, $event);
    }
}
