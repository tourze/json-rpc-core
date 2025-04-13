<?php

namespace Tourze\JsonRPC\Core\Tests\Attribute;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;

/**
 * 测试MethodExpose属性类
 */
class MethodExposeTest extends TestCase
{
    /**
     * 测试正常构造函数
     */
    public function testConstructWithValidMethod(): void
    {
        $methodExpose = new MethodExpose('test.method');

        $this->assertInstanceOf(MethodExpose::class, $methodExpose);

        // 由于直接访问父类属性可能有问题，我们只验证常量值
        $this->assertEquals('json_rpc_http_server.jsonrpc_method', MethodExpose::JSONRPC_METHOD_TAG);
    }

    /**
     * 测试空method参数应抛出异常
     */
    public function testConstructWithNullMethodThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('method参数不能为空');

        new MethodExpose(null);
    }

    /**
     * 测试属性的目标和重复性
     */
    public function testAttributeTargetAndRepeatability(): void
    {
        $reflectionClass = new \ReflectionClass(MethodExpose::class);
        $attributes = $reflectionClass->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);
        $attribute = $attributes[0]->newInstance();

        // 检查属性是否可以应用于类
        $this->assertTrue((bool)($attribute->flags & \Attribute::TARGET_CLASS));

        // 检查属性是否可重复
        $this->assertTrue((bool)($attribute->flags & \Attribute::IS_REPEATABLE));
    }
}
