<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Event\AbstractOnBatchSubRequestProcessEvent;
use Tourze\JsonRPC\Core\Event\BatchSubRequestProcessedEvent;
use Tourze\JsonRPC\Core\Event\JsonRpcServerEvent;
use Tourze\JsonRPC\Core\Event\OnBatchSubRequestProcessingEvent;

/**
 * 测试OnBatchSubRequestProcessingEvent批量子请求处理中事件.
 *
 * @internal
 */
#[CoversClass(OnBatchSubRequestProcessingEvent::class)]
final class OnBatchSubRequestProcessingEventTest extends TestCase
{
    public function testEventCreation(): void
    {
        $event = new OnBatchSubRequestProcessingEvent();

        $this->assertInstanceOf(OnBatchSubRequestProcessingEvent::class, $event);
    }

    public function testEventInheritsFromAbstractOnBatchSubRequestProcessEvent(): void
    {
        $event = new OnBatchSubRequestProcessingEvent();

        $this->assertInstanceOf(AbstractOnBatchSubRequestProcessEvent::class, $event);
    }

    public function testEventImplementsJsonRpcServerEvent(): void
    {
        $event = new OnBatchSubRequestProcessingEvent();

        $this->assertInstanceOf(JsonRpcServerEvent::class, $event);
    }

    public function testItemPositionProperty(): void
    {
        $event = new OnBatchSubRequestProcessingEvent();
        $position = 2;

        $event->setItemPosition($position);

        $this->assertEquals($position, $event->getItemPosition());
    }

    public function testEventLifecycle(): void
    {
        $event = new OnBatchSubRequestProcessingEvent();

        // 模拟批量请求中即将处理第1个子请求
        $position = 1;
        $event->setItemPosition($position);

        // 验证事件状态
        $this->assertEquals($position, $event->getItemPosition());
        $this->assertInstanceOf(JsonRpcServerEvent::class, $event);
    }

    public function testMultiplePositions(): void
    {
        $event = new OnBatchSubRequestProcessingEvent();

        $positions = [0, 1, 3, 7, 15];

        foreach ($positions as $position) {
            $event->setItemPosition($position);
            $this->assertEquals($position, $event->getItemPosition());
        }
    }

    public function testEventNamespaceAndClassStructure(): void
    {
        $event = new OnBatchSubRequestProcessingEvent();

        $reflection = new \ReflectionClass($event);

        $this->assertEquals('Tourze\JsonRPC\Core\Event', $reflection->getNamespaceName());
        $this->assertEquals('OnBatchSubRequestProcessingEvent', $reflection->getShortName());
        $this->assertTrue($reflection->isInstantiable());
        $this->assertFalse($reflection->isAbstract());
    }

    public function testEventDocumentation(): void
    {
        $reflection = new \ReflectionClass(OnBatchSubRequestProcessingEvent::class);
        $docComment = $reflection->getDocComment();

        $this->assertNotFalse($docComment);
        $this->assertStringContainsString('在子请求被处理之前触发', $docComment);
    }

    public function testEventSequence(): void
    {
        // 模拟批量请求处理序列
        $processingEvent = new OnBatchSubRequestProcessingEvent();
        $processedEvent = new BatchSubRequestProcessedEvent();

        // 设置相同的位置
        $position = 5;
        $processingEvent->setItemPosition($position);
        $processedEvent->setItemPosition($position);

        // 验证两个事件都有相同的位置信息
        $this->assertEquals($position, $processingEvent->getItemPosition());
        $this->assertEquals($position, $processedEvent->getItemPosition());

        // 验证它们都继承自同一个抽象类
        $this->assertInstanceOf(
            AbstractOnBatchSubRequestProcessEvent::class,
            $processingEvent
        );
        $this->assertInstanceOf(
            AbstractOnBatchSubRequestProcessEvent::class,
            $processedEvent
        );
    }

    public function testEventUsageScenario(): void
    {
        $event = new OnBatchSubRequestProcessingEvent();

        // 模拟处理批量请求中的第3个子请求
        $event->setItemPosition(3);

        // 验证事件可以用于日志记录或拦截
        $this->assertEquals(3, $event->getItemPosition());
        $this->assertInstanceOf(JsonRpcServerEvent::class, $event);
    }
}
