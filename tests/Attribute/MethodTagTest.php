<?php

namespace Tourze\JsonRPC\Core\Tests\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Attribute\MethodTag;

/**
 * 测试MethodTag属性类.
 *
 * @internal
 */
#[CoversClass(MethodTag::class)]
final class MethodTagTest extends TestCase
{
    /**
     * 测试构造函数默认值
     */
    public function testConstructWithDefaultValues(): void
    {
        $methodTag = new MethodTag();

        $this->assertInstanceOf(MethodTag::class, $methodTag);
        $this->assertEquals('', $methodTag->name);
    }

    /**
     * 测试构造函数自定义值
     */
    public function testConstructWithCustomValues(): void
    {
        $name = '测试标签';

        $methodTag = new MethodTag($name);

        $this->assertInstanceOf(MethodTag::class, $methodTag);
        $this->assertEquals($name, $methodTag->name);
    }

    /**
     * 测试属性的目标和重复性.
     */
    public function testAttributeTargetAndRepeatability(): void
    {
        $reflectionClass = new \ReflectionClass(MethodTag::class);
        $attributes = $reflectionClass->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);
        $attribute = $attributes[0]->newInstance();

        // 检查属性是否可以应用于类
        $this->assertTrue((bool) ($attribute->flags & \Attribute::TARGET_CLASS));

        // 检查属性是否可重复
        $this->assertTrue((bool) ($attribute->flags & \Attribute::IS_REPEATABLE));
    }
}
