<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Domain;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodParamsValidatorInterface;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodResolverInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithResultDocInterface;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

class MethodInterfacesTest extends TestCase
{
    /**
     * 测试方法参数验证器接口
     */
    public function testMethodParamsValidatorInterface(): void
    {
        $validator = new class implements JsonRpcMethodParamsValidatorInterface {
            public function validate(JsonRpcRequest $jsonRpcRequest, JsonRpcMethodInterface $method): array
            {
                // 返回一个空的违规列表，表示没有错误
                return [];
            }
        };

        $request = new JsonRpcRequest();
        $request->setMethod('test.method');
        $request->setParams(new JsonRpcParams(['test' => 'value']));

        $method = new class implements JsonRpcMethodInterface {
            public function __invoke(JsonRpcRequest $request): mixed
            {
                return ['success' => true];
            }

            public function execute(): array
            {
                return ['success' => true];
            }
        };

        // 确保验证方法可以被调用，并返回空数组（没有错误）
        $violations = $validator->validate($request, $method);
        $this->assertIsArray($violations);
        $this->assertEmpty($violations);
    }

    /**
     * 测试方法解析器接口
     */
    public function testMethodResolverInterface(): void
    {
        $testMethod = new class implements JsonRpcMethodInterface {
            public function __invoke(JsonRpcRequest $request): mixed
            {
                return ['success' => true];
            }

            public function execute(): array
            {
                return ['success' => true];
            }
        };

        $resolver = new class($testMethod) implements JsonRpcMethodResolverInterface {
            public function __construct(private readonly JsonRpcMethodInterface $testMethod)
            {
            }

            public function resolve(string $methodName): JsonRpcMethodInterface
            {
                return $this->testMethod;
            }
        };

        $method = $resolver->resolve('test.method');

        $this->assertInstanceOf(JsonRpcMethodInterface::class, $method);

        $request = new JsonRpcRequest();
        $request->setMethod('test.method');
        $request->setParams(new JsonRpcParams());

        $result = $method($request);
        $this->assertEquals(['success' => true], $result);
    }

    /**
     * 测试带有结果文档的方法接口
     */
    public function testMethodWithResultDocInterface(): void
    {
        $method = new class implements JsonRpcMethodInterface, MethodWithResultDocInterface {
            public function __invoke(JsonRpcRequest $request): mixed
            {
                return ['success' => true];
            }

            public function execute(): array
            {
                return ['success' => true];
            }

            public function resultDoc(): string
            {
                return 'Returns a success indicator.';
            }
        };

        $this->assertEquals('Returns a success indicator.', $method->resultDoc());
    }
}
