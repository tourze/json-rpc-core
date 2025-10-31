<?php

namespace Tourze\JsonRPC\Core\Tests\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;

/**
 * 测试MethodDoc属性类.
 *
 * @internal
 */
#[CoversClass(MethodDoc::class)]
final class MethodDocTest extends TestCase
{
    /**
     * 测试构造函数默认值
     */
    public function testConstructWithDefaultValues(): void
    {
        $methodDoc = new MethodDoc();

        $this->assertInstanceOf(MethodDoc::class, $methodDoc);
        $this->assertEquals('', $methodDoc->summary);
        $this->assertEquals('', $methodDoc->description);
    }

    /**
     * 测试构造函数自定义值
     */
    public function testConstructWithCustomValues(): void
    {
        $summary = '方法概要';
        $description = '方法详细描述';

        $methodDoc = new MethodDoc($summary, $description);

        $this->assertInstanceOf(MethodDoc::class, $methodDoc);
        $this->assertEquals($summary, $methodDoc->summary);
        $this->assertEquals($description, $methodDoc->description);
    }

    /**
     * 测试属性的目标.
     */
    public function testAttributeTarget(): void
    {
        $reflectionClass = new \ReflectionClass(MethodDoc::class);
        $attributes = $reflectionClass->getAttributes(\Attribute::class);

        $this->assertCount(1, $attributes);
        $attribute = $attributes[0]->newInstance();

        // 检查属性是否只应用于类
        $this->assertTrue((bool) ($attribute->flags & \Attribute::TARGET_CLASS));
        $this->assertFalse((bool) ($attribute->flags & \Attribute::TARGET_PROPERTY));
        $this->assertFalse((bool) ($attribute->flags & \Attribute::TARGET_METHOD));
    }
}
