<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Event;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Event\MethodExecutingEvent;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;

/**
 * 测试MethodExecutingEvent方法执行中事件
 */
class MethodExecutingEventTest extends TestCase
{
    public function testSetAndGetItem(): void
    {
        $event = new MethodExecutingEvent();
        $request = new JsonRpcRequest();
        $request->setMethod('user.create');
        $request->setId('123');
        $request->setParams(new JsonRpcParams(['name' => 'John']));

        $event->setItem($request);

        $this->assertSame($request, $event->getItem());
        $this->assertEquals('user.create', $event->getItem()->getMethod());
        $this->assertEquals('123', $event->getItem()->getId());
    }

    public function testSetAndGetResponse(): void
    {
        $event = new MethodExecutingEvent();
        $response = new JsonRpcResponse();
        $response->setResult(['success' => true]);
        $response->setId('456');

        // 初始值应该为null
        $this->assertNull($event->getResponse());

        $event->setResponse($response);

        $this->assertSame($response, $event->getResponse());
        $this->assertEquals(['success' => true], $event->getResponse()->getResult());
        $this->assertEquals('456', $event->getResponse()->getId());
    }

    public function testResponseCanBeSetToNull(): void
    {
        $event = new MethodExecutingEvent();
        $response = new JsonRpcResponse();
        $response->setResult(['data' => 'test']);

        $event->setResponse($response);
        $this->assertSame($response, $event->getResponse());

        $event->setResponse(null);
        $this->assertNull($event->getResponse());
    }

    public function testCompleteEventSetup(): void
    {
        $event = new MethodExecutingEvent();
        
        // 创建请求
        $request = new JsonRpcRequest();
        $request->setMethod('product.search');
        $request->setId('search-123');
        $request->setParams(new JsonRpcParams(['query' => 'laptop', 'limit' => 10]));

        // 创建响应
        $response = new JsonRpcResponse();
        $response->setResult(['products' => [['id' => 1, 'name' => 'Laptop']]]);
        $response->setId('search-123');

        $event->setItem($request);
        $event->setResponse($response);

        // 验证所有属性
        $this->assertEquals('product.search', $event->getItem()->getMethod());
        $this->assertEquals('search-123', $event->getItem()->getId());
        $this->assertEquals(['query' => 'laptop', 'limit' => 10], $event->getItem()->getParams()->all());
        $this->assertEquals(['products' => [['id' => 1, 'name' => 'Laptop']]], $event->getResponse()->getResult());
        $this->assertEquals('search-123', $event->getResponse()->getId());
    }

    public function testEventInheritance(): void
    {
        $event = new MethodExecutingEvent();

        $this->assertInstanceOf(\Symfony\Contracts\EventDispatcher\Event::class, $event);
    }

    public function testEventWithNotificationRequest(): void
    {
        $event = new MethodExecutingEvent();
        $request = new JsonRpcRequest();
        $request->setMethod('log.info');
        $request->setParams(new JsonRpcParams(['message' => 'User logged in']));
        // 不设置ID，使其成为通知

        $event->setItem($request);

        $this->assertTrue($event->getItem()->isNotification());
        $this->assertEquals('log.info', $event->getItem()->getMethod());
        $this->assertNull($event->getResponse()); // 通知通常不需要响应
    }

    public function testEventWithErrorResponse(): void
    {
        $event = new MethodExecutingEvent();
        
        $request = new JsonRpcRequest();
        $request->setMethod('invalid.method');
        $request->setId('error-test');
        
        $response = new JsonRpcResponse();
        $error = new \Tourze\JsonRPC\Core\Exception\JsonRpcMethodNotFoundException('invalid.method', [
            'method' => 'invalid.method'
        ]);
        $response->setError($error);
        $response->setId('error-test');

        $event->setItem($request);
        $event->setResponse($response);

        $this->assertEquals('invalid.method', $event->getItem()->getMethod());
        $this->assertNotNull($event->getResponse()->getError());
        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Exception\JsonRpcExceptionInterface::class, $event->getResponse()->getError());
        $this->assertEquals('Method not found', $event->getResponse()->getError()->getErrorMessage());
    }

    public function testMultipleRequestUpdates(): void
    {
        $event = new MethodExecutingEvent();

        // 第一个请求
        $request1 = new JsonRpcRequest();
        $request1->setMethod('method1');
        $request1->setId('1');
        
        $event->setItem($request1);
        $this->assertEquals('method1', $event->getItem()->getMethod());

        // 第二个请求
        $request2 = new JsonRpcRequest();
        $request2->setMethod('method2');
        $request2->setId('2');
        
        $event->setItem($request2);
        $this->assertEquals('method2', $event->getItem()->getMethod());
        $this->assertEquals('2', $event->getItem()->getId());
    }

    public function testEventClassStructure(): void
    {
        $event = new MethodExecutingEvent();
        $reflection = new \ReflectionClass($event);
        
        $this->assertEquals('Tourze\JsonRPC\Core\Event', $reflection->getNamespaceName());
        $this->assertEquals('MethodExecutingEvent', $reflection->getShortName());
        $this->assertFalse($reflection->isAbstract());
        $this->assertTrue($reflection->isInstantiable());
        
        // 验证有正确的属性
        $this->assertTrue($reflection->hasProperty('item'));
        $this->assertTrue($reflection->hasProperty('response'));
    }
} 