<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Event;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Event\OnExceptionEvent;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * 测试OnExceptionEvent异常事件
 */
class OnExceptionEventTest extends TestCase
{
    public function testSetAndGetException(): void
    {
        $event = new OnExceptionEvent();
        $exception = new \RuntimeException('Test exception', 500);

        $event->setException($exception);

        $this->assertSame($exception, $event->getException());
        $this->assertEquals('Test exception', $event->getException()->getMessage());
        $this->assertEquals(500, $event->getException()->getCode());
    }

    public function testSetExceptionChaining(): void
    {
        $event = new OnExceptionEvent();
        $exception = new \InvalidArgumentException('Invalid argument');

        $returnedEvent = $event->setException($exception);

        $this->assertSame($event, $returnedEvent);
        $this->assertSame($exception, $event->getException());
    }

    public function testSetAndGetFromJsonRpcRequest(): void
    {
        $event = new OnExceptionEvent();
        $request = new JsonRpcRequest();
        $request->setMethod('user.create');
        $request->setId('test-123');
        $request->setParams(new JsonRpcParams(['name' => 'John']));

        // 初始值应该为null
        $this->assertNull($event->getFromJsonRpcRequest());

        $event->setFromJsonRpcRequest($request);

        $this->assertSame($request, $event->getFromJsonRpcRequest());
        $this->assertEquals('user.create', $event->getFromJsonRpcRequest()->getMethod());
        $this->assertEquals('test-123', $event->getFromJsonRpcRequest()->getId());
    }

    public function testFromJsonRpcRequestCanBeSetToNull(): void
    {
        $event = new OnExceptionEvent();
        $request = new JsonRpcRequest();
        $request->setMethod('test.method');

        $event->setFromJsonRpcRequest($request);
        $this->assertSame($request, $event->getFromJsonRpcRequest());

        $event->setFromJsonRpcRequest(null);
        $this->assertNull($event->getFromJsonRpcRequest());
    }

    public function testCompleteExceptionEvent(): void
    {
        $event = new OnExceptionEvent();
        
        $exception = new \RuntimeException('Database connection failed', 1001);
        $request = new JsonRpcRequest();
        $request->setMethod('data.fetch');
        $request->setId('error-456');
        $request->setParams(new JsonRpcParams(['table' => 'users']));

        $event->setException($exception);
        $event->setFromJsonRpcRequest($request);

        // 验证所有属性
        $this->assertEquals('Database connection failed', $event->getException()->getMessage());
        $this->assertEquals(1001, $event->getException()->getCode());
        $this->assertEquals('data.fetch', $event->getFromJsonRpcRequest()->getMethod());
        $this->assertEquals('error-456', $event->getFromJsonRpcRequest()->getId());
        $this->assertEquals(['table' => 'users'], $event->getFromJsonRpcRequest()->getParams()->all());
    }

    public function testEventImplementsJsonRpcServerEvent(): void
    {
        $event = new OnExceptionEvent();

        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Event\JsonRpcServerEvent::class, $event);
    }

    public function testDifferentExceptionTypes(): void
    {
        $event = new OnExceptionEvent();

        // 测试RuntimeException
        $runtimeException = new \RuntimeException('Runtime error');
        $event->setException($runtimeException);
        $this->assertInstanceOf(\RuntimeException::class, $event->getException());

        // 测试InvalidArgumentException
        $invalidArgException = new \InvalidArgumentException('Invalid argument error');
        $event->setException($invalidArgException);
        $this->assertInstanceOf(\InvalidArgumentException::class, $event->getException());

        // 测试LogicException
        $logicException = new \LogicException('Logic error');
        $event->setException($logicException);
        $this->assertInstanceOf(\LogicException::class, $event->getException());

        // 测试Error
        $error = new \Error('Fatal error');
        $event->setException($error);
        $this->assertInstanceOf(\Error::class, $event->getException());
    }

    public function testEventWithoutRequest(): void
    {
        $event = new OnExceptionEvent();
        $exception = new \RuntimeException('General system error');

        $event->setException($exception);
        // 不设置请求，模拟系统级异常

        $this->assertEquals('General system error', $event->getException()->getMessage());
        $this->assertNull($event->getFromJsonRpcRequest());
    }

    public function testEventWithNotificationRequest(): void
    {
        $event = new OnExceptionEvent();
        
        $exception = new \RuntimeException('Notification processing failed');
        $request = new JsonRpcRequest();
        $request->setMethod('log.error');
        $request->setParams(new JsonRpcParams(['level' => 'critical']));
        // 不设置ID，使其成为通知

        $event->setException($exception);
        $event->setFromJsonRpcRequest($request);

        $this->assertTrue($event->getFromJsonRpcRequest()->isNotification());
        $this->assertEquals('log.error', $event->getFromJsonRpcRequest()->getMethod());
        $this->assertEquals('Notification processing failed', $event->getException()->getMessage());
    }

    public function testEventClassStructure(): void
    {
        $event = new OnExceptionEvent();
        $reflection = new \ReflectionClass($event);
        
        $this->assertEquals('Tourze\JsonRPC\Core\Event', $reflection->getNamespaceName());
        $this->assertEquals('OnExceptionEvent', $reflection->getShortName());
        $this->assertFalse($reflection->isAbstract());
        $this->assertTrue($reflection->isInstantiable());
        
        // 验证有正确的属性
        $this->assertTrue($reflection->hasProperty('exception'));
        $this->assertTrue($reflection->hasProperty('fromJsonRpcRequest'));
    }

    public function testExceptionWithPreviousException(): void
    {
        $event = new OnExceptionEvent();
        
        $previousException = new \InvalidArgumentException('Invalid parameter');
        $mainException = new \RuntimeException('Processing failed', 0, $previousException);
        
        $event->setException($mainException);
        
        $this->assertEquals('Processing failed', $event->getException()->getMessage());
        $this->assertSame($previousException, $event->getException()->getPrevious());
        $this->assertEquals('Invalid parameter', $event->getException()->getPrevious()->getMessage());
    }

    public function testEventDocumentation(): void
    {
        $reflection = new \ReflectionClass(OnExceptionEvent::class);
        $docComment = $reflection->getDocComment();
        
        $this->assertNotFalse($docComment);
        $this->assertStringContainsString('Dispatched when an exception occurred during sdk execution', $docComment);
    }
} 