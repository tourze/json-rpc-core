<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Event\AbstractOnBatchSubRequestProcessEvent;
use Tourze\JsonRPC\Core\Event\AbstractOnMethodEvent;
use Tourze\JsonRPC\Core\Event\BatchSubRequestProcessedEvent;
use Tourze\JsonRPC\Core\Event\JsonRpcServerEvent;

/**
 * 测试JsonRpcServerEvent服务器事件接口.
 *
 * @internal
 */
#[CoversClass(JsonRpcServerEvent::class)]
final class JsonRpcServerEventTest extends TestCase
{
    private function createMockServerEvent(): JsonRpcServerEvent
    {
        return new class implements JsonRpcServerEvent {
            public string $eventData = 'test';
        };
    }

    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(JsonRpcServerEvent::class));
    }

    public function testInterfaceImplementation(): void
    {
        $event = $this->createMockServerEvent();

        $this->assertInstanceOf(JsonRpcServerEvent::class, $event);
    }

    public function testInterfaceIsEmpty(): void
    {
        $reflection = new \ReflectionClass(JsonRpcServerEvent::class);

        $methods = $reflection->getMethods();
        $this->assertEmpty($methods, 'JsonRpcServerEvent接口应该是空的，作为标记接口使用');
    }

    public function testInterfaceStructure(): void
    {
        $reflection = new \ReflectionClass(JsonRpcServerEvent::class);

        $this->assertTrue($reflection->isInterface());
        $this->assertEquals('Tourze\JsonRPC\Core\Event', $reflection->getNamespaceName());
        $this->assertEquals('JsonRpcServerEvent', $reflection->getShortName());
    }

    public function testConcreteImplementation(): void
    {
        $event = $this->createMockServerEvent();

        // 验证可以实例化具体实现
        $this->assertInstanceOf(JsonRpcServerEvent::class, $event);

        // 使用反射访问匿名类的属性
        $reflection = new \ReflectionClass($event);
        $this->assertTrue($reflection->hasProperty('eventData'));
        $property = $reflection->getProperty('eventData');
        $this->assertEquals('test', $property->getValue($event));
    }

    public function testMultipleImplementations(): void
    {
        // 测试多个不同的实现
        $implementation1 = new class implements JsonRpcServerEvent {
            public string $type = 'request';
        };

        $implementation2 = new class implements JsonRpcServerEvent {
            public string $type = 'response';
        };

        $this->assertInstanceOf(JsonRpcServerEvent::class, $implementation1);
        $this->assertInstanceOf(JsonRpcServerEvent::class, $implementation2);
        $this->assertEquals('request', $implementation1->type);
        $this->assertEquals('response', $implementation2->type);
    }

    public function testInterfaceAsTypeHint(): void
    {
        $handler = function (JsonRpcServerEvent $event): string {
            return 'handled';
        };

        $event = $this->createMockServerEvent();
        $result = $handler($event);

        $this->assertEquals('handled', $result);
    }

    /**
     * 验证JsonRpcServerEvent被其他事件类实现.
     */
    public function testExistingEventClassesImplementInterface(): void
    {
        $eventClasses = [
            BatchSubRequestProcessedEvent::class,
            AbstractOnMethodEvent::class,
            AbstractOnBatchSubRequestProcessEvent::class,
        ];

        foreach ($eventClasses as $eventClass) {
            $reflection = new \ReflectionClass($eventClass);
            $interfaces = $reflection->getInterfaceNames();

            $this->assertContains(JsonRpcServerEvent::class, $interfaces,
                "{$eventClass} 应该实现 JsonRpcServerEvent 接口");
        }
    }
}
