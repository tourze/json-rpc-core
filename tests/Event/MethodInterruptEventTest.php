<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\EventDispatcher\Event;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Result\SuccessResult;
use Tourze\JsonRPC\Core\Event\AfterMethodApplyEvent;
use Tourze\JsonRPC\Core\Event\BeforeMethodApplyEvent;
use Tourze\JsonRPC\Core\Event\MethodInterruptEvent;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * 测试MethodInterruptEvent方法拦截事件.
 *
 * @internal
 */
#[CoversClass(MethodInterruptEvent::class)]
final class MethodInterruptEventTest extends AbstractEventTestCase
{
    private function createMockMethod(): JsonRpcMethodInterface
    {
        return new class implements JsonRpcMethodInterface {
            public function __invoke(JsonRpcRequest $request): RpcResultInterface
            {
                return new SuccessResult(success: true, message: 'intercepted');
            }

            public function execute(RpcParamInterface $param): RpcResultInterface
            {
                return new SuccessResult(success: true, message: 'intercepted');
            }
        };
    }

    private function createConcreteInterruptEvent(): MethodInterruptEvent
    {
        return new class extends MethodInterruptEvent {
            // 具体实现用于测试抽象类
        };
    }

    public function testSetAndGetMethod(): void
    {
        $event = $this->createConcreteInterruptEvent();
        $method = $this->createMockMethod();

        $event->setMethod($method);

        $this->assertSame($method, $event->getMethod());
    }

    public function testEventIsAbstract(): void
    {
        $reflection = new \ReflectionClass(MethodInterruptEvent::class);

        $this->assertTrue($reflection->isAbstract());
        $this->assertFalse($reflection->isInterface());
    }

    public function testEventInheritance(): void
    {
        $event = $this->createConcreteInterruptEvent();

        $this->assertInstanceOf(Event::class, $event);
        $this->assertInstanceOf(MethodInterruptEvent::class, $event);
    }

    public function testMethodInteraction(): void
    {
        $event = $this->createConcreteInterruptEvent();
        $method = $this->createMockMethod();

        $event->setMethod($method);

        // 验证方法可以被调用
        $request = new JsonRpcRequest();
        $result = $event->getMethod()($request);

        $this->assertInstanceOf(RpcResultInterface::class, $result);
        $this->assertInstanceOf(SuccessResult::class, $result);
        $this->assertSame('intercepted', $result->message);
    }

    public function testMultipleMethodAssignments(): void
    {
        $event = $this->createConcreteInterruptEvent();

        $method1 = $this->createMockMethod();
        $method2 = new class implements JsonRpcMethodInterface {
            public function __invoke(JsonRpcRequest $request): RpcResultInterface
            {
                return new SuccessResult(success: true, message: 'method2');
            }

            public function execute(RpcParamInterface $param): RpcResultInterface
            {
                return new SuccessResult(success: true, message: 'method2');
            }
        };

        $event->setMethod($method1);
        $this->assertSame($method1, $event->getMethod());

        $event->setMethod($method2);
        $this->assertSame($method2, $event->getMethod());
    }

    public function testEventClassStructure(): void
    {
        $reflection = new \ReflectionClass(MethodInterruptEvent::class);

        $this->assertEquals('Tourze\JsonRPC\Core\Event', $reflection->getNamespaceName());
        $this->assertEquals('MethodInterruptEvent', $reflection->getShortName());
        $this->assertTrue($reflection->isAbstract());

        // 验证有正确的属性
        $this->assertTrue($reflection->hasProperty('method'));

        // 验证有正确的方法
        $this->assertTrue($reflection->hasMethod('getMethod'));
        $this->assertTrue($reflection->hasMethod('setMethod'));
    }

    public function testMethodProperty(): void
    {
        $reflection = new \ReflectionClass(MethodInterruptEvent::class);
        $methodProperty = $reflection->getProperty('method');

        $this->assertTrue($methodProperty->isPrivate());
        $reflectionType = $methodProperty->getType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $reflectionType);
        $this->assertEquals(JsonRpcMethodInterface::class, $reflectionType->getName());
    }

    /**
     * 验证具体实现类继承MethodInterruptEvent.
     */
    public function testConcreteImplementations(): void
    {
        // 检查已知的继承类
        $concreteClasses = [
            BeforeMethodApplyEvent::class,
            AfterMethodApplyEvent::class,
        ];

        foreach ($concreteClasses as $className) {
            $reflection = new \ReflectionClass($className);
            $this->assertTrue($reflection->isSubclassOf(MethodInterruptEvent::class),
                "{$className} 应该继承自 MethodInterruptEvent");
        }
    }

    public function testMethodReturnValue(): void
    {
        $event = $this->createConcreteInterruptEvent();
        $method = $this->createMockMethod();

        $event->setMethod($method);

        // 测试方法的 __invoke 方法（execute 需要参数对象）
        $request = new JsonRpcRequest();
        $result = $event->getMethod()($request);
        $this->assertInstanceOf(RpcResultInterface::class, $result);
        $this->assertInstanceOf(SuccessResult::class, $result);
        $this->assertSame('intercepted', $result->message);
    }

    public function testEventDocumentation(): void
    {
        $reflection = new \ReflectionClass(MethodInterruptEvent::class);
        $docComment = $reflection->getDocComment();

        $this->assertNotFalse($docComment);
        $this->assertStringContainsString('通用的方法拦截事件', $docComment);
    }
}
