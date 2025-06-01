<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Event;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Event\AbstractOnBatchSubRequestProcessEvent;

/**
 * 测试AbstractOnBatchSubRequestProcessEvent抽象事件类
 */
class AbstractOnBatchSubRequestProcessEventTest extends TestCase
{
    private function createConcreteEvent(): AbstractOnBatchSubRequestProcessEvent
    {
        return new class extends AbstractOnBatchSubRequestProcessEvent {
            // 具体实现用于测试
        };
    }

    public function testSetAndGetItemPosition(): void
    {
        $event = $this->createConcreteEvent();
        $position = 3;

        $event->setItemPosition($position);

        $this->assertEquals($position, $event->getItemPosition());
    }

    public function testItemPositionWithZero(): void
    {
        $event = $this->createConcreteEvent();
        $position = 0;

        $event->setItemPosition($position);

        $this->assertEquals($position, $event->getItemPosition());
    }

    public function testItemPositionWithLargeNumber(): void
    {
        $event = $this->createConcreteEvent();
        $position = 999;

        $event->setItemPosition($position);

        $this->assertEquals($position, $event->getItemPosition());
    }

    public function testEventImplementsJsonRpcServerEvent(): void
    {
        $event = $this->createConcreteEvent();

        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Event\JsonRpcServerEvent::class, $event);
    }

    public function testMultiplePositionUpdates(): void
    {
        $event = $this->createConcreteEvent();

        $event->setItemPosition(1);
        $this->assertEquals(1, $event->getItemPosition());

        $event->setItemPosition(5);
        $this->assertEquals(5, $event->getItemPosition());

        $event->setItemPosition(0);
        $this->assertEquals(0, $event->getItemPosition());
    }
} 