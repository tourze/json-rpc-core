<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Procedure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPC\Core\Result\EmptyResult;
use Tourze\JsonRPC\Core\Result\SuccessResult;

/**
 * JsonRpcMethodInterface 返回类型测试
 *
 * 验证 execute 和 __invoke 方法的返回类型约束
 */
#[CoversClass(JsonRpcMethodInterface::class)]
class BaseProcedureResultTest extends TestCase
{
    public function testJsonRpcMethodInterfaceInvokeReturnsRpcResultInterface(): void
    {
        $reflection = new \ReflectionMethod(JsonRpcMethodInterface::class, '__invoke');
        $returnType = $reflection->getReturnType();

        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertSame(RpcResultInterface::class, $returnType->getName());
    }

    public function testJsonRpcMethodInterfaceExecuteReturnsRpcResultInterface(): void
    {
        $reflection = new \ReflectionMethod(JsonRpcMethodInterface::class, 'execute');
        $returnType = $reflection->getReturnType();

        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertSame(RpcResultInterface::class, $returnType->getName());
    }

    public function testBaseProcedureExecuteReturnsRpcResultInterface(): void
    {
        $reflection = new \ReflectionMethod(BaseProcedure::class, 'execute');
        $returnType = $reflection->getReturnType();

        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertSame(RpcResultInterface::class, $returnType->getName());
    }

    public function testBaseProcedureInvokeReturnsRpcResultInterface(): void
    {
        $reflection = new \ReflectionMethod(BaseProcedure::class, '__invoke');
        $returnType = $reflection->getReturnType();

        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertSame(RpcResultInterface::class, $returnType->getName());
    }

    public function testConcreteResultTypesAreCovariant(): void
    {
        // 验证具体的 Result 类型可以作为返回值（协变返回类型）
        $this->assertTrue(is_a(EmptyResult::class, RpcResultInterface::class, true));
        $this->assertTrue(is_a(SuccessResult::class, RpcResultInterface::class, true));
    }

    public function testExecuteMethodIsAbstract(): void
    {
        $reflection = new \ReflectionMethod(BaseProcedure::class, 'execute');
        $this->assertTrue($reflection->isAbstract());
    }

    public function testExecuteMethodAcceptsRpcParamInterface(): void
    {
        $reflection = new \ReflectionMethod(BaseProcedure::class, 'execute');
        $params = $reflection->getParameters();

        $this->assertCount(1, $params);
        $paramType = $params[0]->getType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $paramType);
        $this->assertSame(RpcParamInterface::class, $paramType->getName());
    }
}
