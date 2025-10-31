<?php

namespace Tourze\JsonRPC\Core\Tests\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Attribute\MethodReturn;

/**
 * 测试MethodReturn属性类.
 *
 * @internal
 */
#[CoversClass(MethodReturn::class)]
final class MethodReturnTest extends TestCase
{
    /**
     * 测试构造函数默认值
     */
    public function testConstructWithDefaultValues(): void
    {
        $methodReturn = new MethodReturn();

        $this->assertInstanceOf(MethodReturn::class, $methodReturn);
        $this->assertEquals('', $methodReturn->description);
    }

    /**
     * 测试构造函数自定义值
     */
    public function testConstructWithCustomValues(): void
    {
        $description = '返回值描述';

        $methodReturn = new MethodReturn($description);

        $this->assertInstanceOf(MethodReturn::class, $methodReturn);
        $this->assertEquals($description, $methodReturn->description);
    }

    /**
     * 测试属性的目标.
     */
    public function testAttributeTarget(): void
    {
        $reflectionClass = new \ReflectionClass(MethodReturn::class);
        $attributes = $reflectionClass->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);
        $attribute = $attributes[0]->newInstance();

        // 检查属性是否只应用于方法
        $this->assertTrue((bool) ($attribute->flags & \Attribute::TARGET_METHOD));
        $this->assertFalse((bool) ($attribute->flags & \Attribute::TARGET_CLASS));
        $this->assertFalse((bool) ($attribute->flags & \Attribute::TARGET_PROPERTY));
    }
}
