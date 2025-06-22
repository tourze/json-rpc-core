<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Event;

use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Event\MethodExecuteFailureEvent;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * 测试MethodExecuteFailureEvent方法执行失败事件
 */
class MethodExecuteFailureEventTest extends TestCase
{
    private function createMockMethod(): JsonRpcMethodInterface
    {
        return new class implements JsonRpcMethodInterface {
            public function __invoke(JsonRpcRequest $request): mixed
            {
                throw new \RuntimeException('Method execution failed');
            }

            public function execute(): array
            {
                throw new \RuntimeException('Method execution failed');
            }
        };
    }

    public function testSetAndGetException(): void
    {
        $event = new MethodExecuteFailureEvent();
        $exception = new \RuntimeException('Test exception', 500);

        $event->setException($exception);

        $this->assertSame($exception, $event->getException());
        $this->assertEquals('Test exception', $event->getException()->getMessage());
        $this->assertEquals(500, $event->getException()->getCode());
    }

    public function testSetExceptionChaining(): void
    {
        $event = new MethodExecuteFailureEvent();
        $exception = new \InvalidArgumentException('Invalid argument');

        $returnedEvent = $event->setException($exception);

        $this->assertSame($event, $returnedEvent);
        $this->assertSame($exception, $event->getException());
    }

    public function testSetAndGetStartTime(): void
    {
        $event = new MethodExecuteFailureEvent();
        $startTime = CarbonImmutable::now();

        $event->setStartTime($startTime);

        $this->assertSame($startTime, $event->getStartTime());
    }

    public function testSetAndGetEndTime(): void
    {
        $event = new MethodExecuteFailureEvent();
        $endTime = CarbonImmutable::now();

        $event->setEndTime($endTime);

        $this->assertSame($endTime, $event->getEndTime());
    }

    public function testCompleteFailureEvent(): void
    {
        $event = new MethodExecuteFailureEvent();
        
        $startTime = CarbonImmutable::now()->subSeconds(1);
        $endTime = CarbonImmutable::now();
        $exception = new \RuntimeException('Database connection failed');
        $method = $this->createMockMethod();
        $request = new JsonRpcRequest();
        $request->setMethod('user.delete');
        $request->setId('fail-test');
        $request->setParams(new JsonRpcParams(['id' => 123]));

        $event->setStartTime($startTime);
        $event->setEndTime($endTime);
        $event->setException($exception);
        $event->setMethod($method);
        $event->setJsonRpcRequest($request);

        // 验证所有属性
        $this->assertEquals($startTime, $event->getStartTime());
        $this->assertEquals($endTime, $event->getEndTime());
        $this->assertSame($exception, $event->getException());
        $this->assertSame($method, $event->getMethod());
        $this->assertSame($request, $event->getJsonRpcRequest());
        $this->assertEquals('Database connection failed', $event->getException()->getMessage());
    }

    public function testEventInheritance(): void
    {
        $event = new MethodExecuteFailureEvent();

        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Event\AbstractOnMethodEvent::class, $event);
        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Event\JsonRpcServerEvent::class, $event);
    }

    public function testDifferentExceptionTypes(): void
    {
        $event = new MethodExecuteFailureEvent();

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
    }

    public function testExecutionDuration(): void
    {
        $event = new MethodExecuteFailureEvent();
        
        $startTime = CarbonImmutable::parse('2023-01-01 10:00:00');
        $endTime = CarbonImmutable::parse('2023-01-01 10:00:03');

        $event->setStartTime($startTime);
        $event->setEndTime($endTime);

        // 验证执行时长
        $duration = $event->getStartTime()->diffInSeconds($event->getEndTime());
        $this->assertEquals(3, $duration);
    }
} 