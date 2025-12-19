<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Contracts;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Tests\Fixtures\DefaultValueTestRpcParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\ReadonlyTestRpcParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\TestRpcParam;

/**
 * RpcParamInterface 契约测试
 *
 * 验证实现此接口的类被框架正确识别
 */
#[CoversClass(RpcParamInterface::class)]
class RpcParamInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(RpcParamInterface::class));
    }

    public function testInterfaceHasNoMethods(): void
    {
        $reflection = new \ReflectionClass(RpcParamInterface::class);
        $methods = $reflection->getMethods();

        $this->assertCount(0, $methods, 'RpcParamInterface 应该是一个标记接口，不应有任何方法定义');
    }

    public function testImplementingClassIsRecognized(): void
    {
        $param = new TestRpcParam('test');

        $this->assertInstanceOf(RpcParamInterface::class, $param);
        $this->assertTrue(is_a($param, RpcParamInterface::class));
        $this->assertTrue(is_a(TestRpcParam::class, RpcParamInterface::class, true));
    }

    public function testNonImplementingClassIsNotRecognized(): void
    {
        $this->assertFalse(is_a(\stdClass::class, RpcParamInterface::class, true));
        $this->assertFalse(is_a('NonExistentClass', RpcParamInterface::class, true));
    }

    public function testReadonlyClassCanImplementInterface(): void
    {
        $param = new ReadonlyTestRpcParam('readonly', 42);

        $this->assertInstanceOf(RpcParamInterface::class, $param);
        $this->assertSame('readonly', $param->name);
        $this->assertSame(42, $param->value);
    }

    public function testImplementingClassWithDefaultValues(): void
    {
        $param = new DefaultValueTestRpcParam();

        $this->assertInstanceOf(RpcParamInterface::class, $param);
        $this->assertSame('default', $param->name);
        $this->assertSame(0, $param->count);
    }
}
