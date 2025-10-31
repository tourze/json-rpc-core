<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\EventDispatcher\Event;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Event\BeforeMethodApplyEvent;
use Tourze\JsonRPC\Core\Event\MethodInterruptEvent;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * 测试BeforeMethodApplyEvent方法执行前事件.
 *
 * @internal
 */
#[CoversClass(BeforeMethodApplyEvent::class)]
final class BeforeMethodApplyEventTest extends AbstractEventTestCase
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

    public function testSetAndGetParams(): void
    {
        $event = new BeforeMethodApplyEvent();
        $params = new JsonRpcParams(['user_id' => 123, 'name' => 'John']);

        $event->setParams($params);

        $this->assertSame($params, $event->getParams());
        $this->assertEquals(['user_id' => 123, 'name' => 'John'], $event->getParams()->all());
    }

    public function testSetAndGetResult(): void
    {
        $event = new BeforeMethodApplyEvent();
        $result = ['processed' => true, 'data' => ['id' => 456]];

        // 初始值应该为null
        $this->assertNull($event->getResult());

        $event->setResult($result);

        $this->assertEquals($result, $event->getResult());
    }

    public function testSetAndGetName(): void
    {
        $event = new BeforeMethodApplyEvent();
        $methodName = 'user.create';

        $event->setName($methodName);

        $this->assertEquals($methodName, $event->getName());
    }

    public function testSetAndGetRequest(): void
    {
        $event = new BeforeMethodApplyEvent();
        $request = new JsonRpcRequest();
        $request->setMethod('user.update');
        $request->setId('789');
        $request->setParams(new JsonRpcParams(['id' => 456, 'email' => 'john@example.com']));

        $event->setRequest($request);

        $this->assertSame($request, $event->getRequest());
        $this->assertEquals('user.update', $event->getRequest()->getMethod());
        $this->assertEquals('789', $event->getRequest()->getId());
    }

    public function testSetAndGetMethod(): void
    {
        $event = new BeforeMethodApplyEvent();
        $method = $this->createMockMethod();

        $event->setMethod($method);

        $this->assertSame($method, $event->getMethod());
    }

    public function testCompleteEventSetup(): void
    {
        $event = new BeforeMethodApplyEvent();

        // 设置所有属性
        $params = new JsonRpcParams(['action' => 'create', 'data' => ['name' => 'Test']]);
        $request = new JsonRpcRequest();
        $request->setMethod('entity.create');
        $request->setId('test-123');
        $request->setParams($params);

        $method = $this->createMockMethod();
        $methodName = 'entity.create';
        $result = ['intercepted' => true];

        $event->setParams($params);
        $event->setRequest($request);
        $event->setMethod($method);
        $event->setName($methodName);
        $event->setResult($result);

        // 验证所有属性
        $this->assertSame($params, $event->getParams());
        $this->assertSame($request, $event->getRequest());
        $this->assertSame($method, $event->getMethod());
        $this->assertEquals($methodName, $event->getName());
        $this->assertEquals($result, $event->getResult());
    }

    public function testEventInheritance(): void
    {
        $event = new BeforeMethodApplyEvent();

        $this->assertInstanceOf(MethodInterruptEvent::class, $event);
        $this->assertInstanceOf(Event::class, $event);
    }

    public function testResultCanBeSetToNull(): void
    {
        $event = new BeforeMethodApplyEvent();

        $event->setResult(['some' => 'data']);
        $this->assertEquals(['some' => 'data'], $event->getResult());

        $event->setResult(null);
        $this->assertNull($event->getResult());
    }

    public function testResultWithDifferentTypes(): void
    {
        $event = new BeforeMethodApplyEvent();

        // 测试字符串结果
        $event->setResult('string result');
        $this->assertEquals('string result', $event->getResult());

        // 测试数组结果
        $event->setResult(['array' => 'result']);
        $this->assertEquals(['array' => 'result'], $event->getResult());

        // 测试数字结果
        $event->setResult(123);
        $this->assertEquals(123, $event->getResult());

        // 测试布尔结果
        $event->setResult(true);
        $this->assertTrue($event->getResult());
    }
}
