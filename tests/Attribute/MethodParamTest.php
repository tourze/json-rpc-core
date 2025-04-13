<?php

namespace Tourze\JsonRPC\Core\Tests\Attribute;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Attribute\MethodParam;

/**
 * 测试MethodParam属性类
 */
class MethodParamTest extends TestCase
{
    /**
     * 测试构造函数默认值
     */
    public function testConstructWithDefaultValues(): void
    {
        $methodParam = new MethodParam();

        $this->assertInstanceOf(MethodParam::class, $methodParam);
        $this->assertEquals('', $methodParam->description);
        $this->assertFalse($methodParam->optional);
    }

    /**
     * 测试构造函数自定义值
     */
    public function testConstructWithCustomValues(): void
    {
        $description = '测试参数描述';
        $optional = true;

        $methodParam = new MethodParam($description, $optional);

        $this->assertInstanceOf(MethodParam::class, $methodParam);
        $this->assertEquals($description, $methodParam->description);
        $this->assertTrue($methodParam->optional);
    }

    /**
     * 测试属性的目标
     */
    public function testAttributeTarget(): void
    {
        $reflectionClass = new \ReflectionClass(MethodParam::class);
        $attributes = $reflectionClass->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);
        $attribute = $attributes[0]->newInstance();

        // 检查属性是否只应用于属性
        $this->assertTrue((bool)($attribute->flags & \Attribute::TARGET_PROPERTY));
        $this->assertFalse((bool)($attribute->flags & \Attribute::TARGET_CLASS));
        $this->assertFalse((bool)($attribute->flags & \Attribute::TARGET_METHOD));
    }
}
