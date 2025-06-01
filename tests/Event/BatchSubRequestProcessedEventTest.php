<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Event;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Event\BatchSubRequestProcessedEvent;

/**
 * 测试BatchSubRequestProcessedEvent批量子请求处理完成事件
 */
class BatchSubRequestProcessedEventTest extends TestCase
{
    public function testEventCreation(): void
    {
        $event = new BatchSubRequestProcessedEvent();

        $this->assertInstanceOf(BatchSubRequestProcessedEvent::class, $event);
    }

    public function testEventInheritsFromAbstractOnBatchSubRequestProcessEvent(): void
    {
        $event = new BatchSubRequestProcessedEvent();

        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Event\AbstractOnBatchSubRequestProcessEvent::class, $event);
    }

    public function testEventImplementsJsonRpcServerEvent(): void
    {
        $event = new BatchSubRequestProcessedEvent();

        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Event\JsonRpcServerEvent::class, $event);
    }

    public function testItemPositionProperty(): void
    {
        $event = new BatchSubRequestProcessedEvent();
        $position = 5;

        $event->setItemPosition($position);

        $this->assertEquals($position, $event->getItemPosition());
    }

    public function testEventLifecycle(): void
    {
        $event = new BatchSubRequestProcessedEvent();
        
        // 模拟批量请求中第3个子请求处理完成
        $position = 3;
        $event->setItemPosition($position);

        // 验证事件状态
        $this->assertEquals($position, $event->getItemPosition());
        // BatchSubRequestProcessedEvent继承自AbstractOnBatchSubRequestProcessEvent，
        // 而AbstractOnBatchSubRequestProcessEvent只实现JsonRpcServerEvent接口，不继承Event
        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Event\JsonRpcServerEvent::class, $event);
    }

    public function testMultiplePositions(): void
    {
        $event = new BatchSubRequestProcessedEvent();
        
        $positions = [0, 1, 5, 10, 99];
        
        foreach ($positions as $position) {
            $event->setItemPosition($position);
            $this->assertEquals($position, $event->getItemPosition());
        }
    }

    public function testEventNamespaceAndClassStructure(): void
    {
        $event = new BatchSubRequestProcessedEvent();
        
        $reflection = new \ReflectionClass($event);
        
        $this->assertEquals('Tourze\JsonRPC\Core\Event', $reflection->getNamespaceName());
        $this->assertEquals('BatchSubRequestProcessedEvent', $reflection->getShortName());
        $this->assertTrue($reflection->isInstantiable());
        $this->assertFalse($reflection->isAbstract());
    }
} 