<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Domain;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodParamsValidatorInterface;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodResolverInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithResultDocInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithValidatedParamsInterface;
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

    /**
     * 测试带有参数验证的方法接口
     */
    public function testMethodWithValidatedParamsInterface(): void
    {
        $method = new class implements JsonRpcMethodInterface, MethodWithValidatedParamsInterface {
            public function __invoke(JsonRpcRequest $request): mixed
            {
                return ['success' => true];
            }

            public function execute(): array
            {
                return ['success' => true];
            }

            public function getParamsConstraint(): Collection
            {
                return new Collection([
                    'fields' => [
                        'username' => [
                            new NotBlank(),
                            new Type('string'),
                        ],
                        'email' => [
                            new NotBlank(),
                            new Type('string'),
                        ],
                    ],
                    'allowExtraFields' => false,
                    'allowMissingFields' => false,
                ]);
            }
        };

        $constraint = $method->getParamsConstraint();

        $this->assertInstanceOf(Collection::class, $constraint);
        $this->assertIsArray($constraint->fields);
        $this->assertArrayHasKey('username', $constraint->fields);
        $this->assertArrayHasKey('email', $constraint->fields);
        $this->assertFalse($constraint->allowExtraFields);
        $this->assertFalse($constraint->allowMissingFields);

        // 验证username字段存在约束条件，但不检查具体类型
        // 因为Symfony Validator可能会将约束条件包装在Required对象中
        $this->assertNotEmpty($constraint->fields['username']);
    }
}
