<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Contracts;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RequestHandlerInterface;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcMethodNotFoundException;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * 测试RequestHandlerInterface接口
 */
class RequestHandlerInterfaceTest extends TestCase
{
    private function createMockMethod(): JsonRpcMethodInterface
    {
        return new class implements JsonRpcMethodInterface {
            public function __invoke(JsonRpcRequest $request): mixed
            {
                return ['echo' => $request->getParams()->all()];
            }

            public function execute(): array
            {
                return ['echo' => []];
            }
        };
    }

    private function createMockHandler(): RequestHandlerInterface
    {
        $mockMethod = $this->createMockMethod();
        
        return new class($mockMethod) implements RequestHandlerInterface {
            public function __construct(private readonly JsonRpcMethodInterface $mockMethod)
            {
            }

            public function resolveMethod(JsonRpcRequest $request): JsonRpcMethodInterface
            {
                $methodName = $request->getMethod();
                
                // 简单的方法解析逻辑
                if (in_array($methodName, ['echo', 'test.method', 'user.create'])) {
                    return $this->mockMethod;
                }
                
                throw new JsonRpcMethodNotFoundException($methodName, [
                    'available_methods' => ['echo', 'test.method', 'user.create']
                ]);
            }
        };
    }

    public function testInterfaceImplementation(): void
    {
        $handler = $this->createMockHandler();

        $this->assertInstanceOf(RequestHandlerInterface::class, $handler);
    }

    public function testResolveMethodWithValidMethod(): void
    {
        $handler = $this->createMockHandler();
        $request = new JsonRpcRequest();
        $request->setMethod('echo');
        $request->setParams(new JsonRpcParams(['test' => 'value']));

        $method = $handler->resolveMethod($request);

        $this->assertInstanceOf(JsonRpcMethodInterface::class, $method);
    }

    public function testResolveMethodWithDifferentValidMethods(): void
    {
        $handler = $this->createMockHandler();
        
        $validMethods = ['echo', 'test.method', 'user.create'];
        
        foreach ($validMethods as $methodName) {
            $request = new JsonRpcRequest();
            $request->setMethod($methodName);
            $request->setParams(new JsonRpcParams());

            $method = $handler->resolveMethod($request);

            $this->assertInstanceOf(JsonRpcMethodInterface::class, $method);
        }
    }

    public function testResolveMethodWithInvalidMethod(): void
    {
        $handler = $this->createMockHandler();
        $request = new JsonRpcRequest();
        $request->setMethod('invalid.method');
        $request->setParams(new JsonRpcParams());

        $this->expectException(JsonRpcMethodNotFoundException::class);
        $this->expectExceptionMessage('Method not found');

        $handler->resolveMethod($request);
    }

    public function testResolveMethodReturnsCallableMethod(): void
    {
        $handler = $this->createMockHandler();
        $request = new JsonRpcRequest();
        $request->setMethod('echo');
        $request->setParams(new JsonRpcParams(['message' => 'Hello']));

        $method = $handler->resolveMethod($request);

        // 验证返回的方法可以被调用
        $result = $method($request);
        $this->assertEquals(['echo' => ['message' => 'Hello']], $result);
    }

    public function testResolveMethodWithComplexRequest(): void
    {
        $handler = $this->createMockHandler();
        $request = new JsonRpcRequest();
        $request->setJsonrpc('2.0');
        $request->setMethod('user.create');
        $request->setId('complex-test');
        $request->setParams(new JsonRpcParams([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'admin'
        ]));

        $method = $handler->resolveMethod($request);

        $this->assertInstanceOf(JsonRpcMethodInterface::class, $method);
        
        // 验证方法可以处理复杂参数
        $result = $method($request);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('echo', $result);
    }

    public function testResolveMethodExceptionContext(): void
    {
        $handler = $this->createMockHandler();
        $request = new JsonRpcRequest();
        $request->setMethod('unknown.method');
        $request->setParams(new JsonRpcParams());

        try {
            $handler->resolveMethod($request);
            $this->fail('Expected JsonRpcMethodNotFoundException to be thrown');
        } catch (JsonRpcMethodNotFoundException $e) {
            $this->assertEquals('unknown.method', $e->getMethodName());
            $this->assertArrayHasKey('available_methods', $e->getContext());
            $this->assertContains('echo', $e->getContext()['available_methods']);
        }
    }

    public function testResolveMethodWithNotificationRequest(): void
    {
        $handler = $this->createMockHandler();
        $request = new JsonRpcRequest();
        $request->setMethod('echo');
        $request->setParams(new JsonRpcParams(['notification' => true]));
        // 不设置ID，使其成为通知

        $this->assertTrue($request->isNotification());
        
        $method = $handler->resolveMethod($request);
        $this->assertInstanceOf(JsonRpcMethodInterface::class, $method);
    }
} 