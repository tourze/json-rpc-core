<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Event;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Event\AbstractOnMethodEvent;
use Tourze\JsonRPC\Core\Event\JsonRpcServerEvent;
use Tourze\JsonRPC\Core\Event\MethodExecuteSuccessEvent;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * 测试MethodExecuteSuccessEvent方法执行成功事件.
 *
 * @internal
 */
#[CoversClass(MethodExecuteSuccessEvent::class)]
final class MethodExecuteSuccessEventTest extends TestCase
{
    private function createMockMethod(): JsonRpcMethodInterface
    {
        return new class implements JsonRpcMethodInterface {
            public function __invoke(JsonRpcRequest $request): mixed
            {
                return ['success' => true];
            }

            public function execute(): array
            {
                return ['success' => true];
            }
        };
    }

    public function testSetAndGetResult(): void
    {
        $event = new MethodExecuteSuccessEvent();
        $result = ['user_id' => 123, 'status' => 'created'];

        // 初始值应该为null
        $this->assertNull($event->getResult());

        $event->setResult($result);

        $this->assertEquals($result, $event->getResult());
    }

    public function testSetAndGetStartTime(): void
    {
        $event = new MethodExecuteSuccessEvent();
        $startTime = CarbonImmutable::now();

        $event->setStartTime($startTime);

        $this->assertSame($startTime, $event->getStartTime());
        $this->assertInstanceOf(CarbonInterface::class, $event->getStartTime());
    }

    public function testSetAndGetEndTime(): void
    {
        $event = new MethodExecuteSuccessEvent();
        $endTime = CarbonImmutable::now();

        $event->setEndTime($endTime);

        $this->assertSame($endTime, $event->getEndTime());
        $this->assertInstanceOf(CarbonInterface::class, $event->getEndTime());
    }

    public function testMethodFromParentClass(): void
    {
        $event = new MethodExecuteSuccessEvent();
        $method = $this->createMockMethod();
        $request = new JsonRpcRequest();
        $request->setMethod('test.method');
        $request->setParams(new JsonRpcParams(['test' => 'value']));

        // 设置继承自AbstractOnMethodEvent的属性
        $event->setMethod($method);
        $event->setJsonRpcRequest($request);

        $this->assertSame($method, $event->getMethod());
        $this->assertSame($request, $event->getJsonRpcRequest());
    }

    public function testCompleteSuccessEvent(): void
    {
        $event = new MethodExecuteSuccessEvent();

        $startTime = CarbonImmutable::now()->subSeconds(2);
        $endTime = CarbonImmutable::now();
        $result = ['executed' => true, 'data' => ['id' => 456]];
        $method = $this->createMockMethod();
        $request = new JsonRpcRequest();
        $request->setMethod('user.create');
        $request->setId('test-id');
        $request->setParams(new JsonRpcParams(['name' => 'John']));

        $event->setStartTime($startTime);
        $event->setEndTime($endTime);
        $event->setResult($result);
        $event->setMethod($method);
        $event->setJsonRpcRequest($request);

        // 验证所有属性
        $this->assertEquals($startTime, $event->getStartTime());
        $this->assertEquals($endTime, $event->getEndTime());
        $this->assertEquals($result, $event->getResult());
        $this->assertSame($method, $event->getMethod());
        $this->assertSame($request, $event->getJsonRpcRequest());
    }

    public function testEventInheritance(): void
    {
        $event = new MethodExecuteSuccessEvent();

        $this->assertInstanceOf(AbstractOnMethodEvent::class, $event);
        $this->assertInstanceOf(JsonRpcServerEvent::class, $event);
    }

    public function testSetResultChaining(): void
    {
        $event = new MethodExecuteSuccessEvent();
        $result = ['chained' => true];

        $event->setResult($result);

        $this->assertEquals($result, $event->getResult());
    }

    public function testResultWithDifferentTypes(): void
    {
        $event = new MethodExecuteSuccessEvent();

        // 测试数组结果
        $event->setResult(['success' => true]);
        $this->assertEquals(['success' => true], $event->getResult());

        // 测试字符串结果
        $event->setResult('operation completed');
        $this->assertEquals('operation completed', $event->getResult());

        // 测试数字结果
        $event->setResult(12345);
        $this->assertEquals(12345, $event->getResult());

        // 测试null结果
        $event->setResult(null);
        $this->assertNull($event->getResult());
    }

    public function testTimeCalculation(): void
    {
        $event = new MethodExecuteSuccessEvent();

        $startTime = CarbonImmutable::parse('2023-01-01 10:00:00');
        $endTime = CarbonImmutable::parse('2023-01-01 10:00:05');

        $event->setStartTime($startTime);
        $event->setEndTime($endTime);

        // 验证时间差 - startTime 到 endTime 的差值
        $duration = $event->getStartTime()->diffInSeconds($event->getEndTime());
        $this->assertEquals(5, $duration);
    }
}
