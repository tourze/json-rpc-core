<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Attribute\ResultProperty;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\ResultWithConstructorAttribute;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\ResultWithPropertyAttribute;

/**
 * ResultProperty 属性注解测试
 */
#[CoversClass(ResultProperty::class)]
class ResultPropertyTest extends TestCase
{
    public function testAttributeIsAttribute(): void
    {
        $reflection = new \ReflectionClass(ResultProperty::class);
        $attributes = $reflection->getAttributes(\Attribute::class);
        $this->assertCount(1, $attributes);
    }

    public function testAttributeTargetsPropertyAndParameter(): void
    {
        $reflection = new \ReflectionClass(ResultProperty::class);
        $attributes = $reflection->getAttributes(\Attribute::class);
        $attribute = $attributes[0]->newInstance();

        $expectedFlags = \Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER;
        $this->assertSame($expectedFlags, $attribute->flags);
    }

    public function testAttributeDefaultValues(): void
    {
        $attr = new ResultProperty();
        $this->assertSame('', $attr->description);
        $this->assertFalse($attr->nullable);
    }

    public function testAttributeWithDescription(): void
    {
        $attr = new ResultProperty(description: '用户ID');
        $this->assertSame('用户ID', $attr->description);
        $this->assertFalse($attr->nullable);
    }

    public function testAttributeWithNullable(): void
    {
        $attr = new ResultProperty(nullable: true);
        $this->assertSame('', $attr->description);
        $this->assertTrue($attr->nullable);
    }

    public function testAttributeWithAllParameters(): void
    {
        $attr = new ResultProperty(description: '邮箱地址', nullable: true);
        $this->assertSame('邮箱地址', $attr->description);
        $this->assertTrue($attr->nullable);
    }

    public function testAttributeCanBeAppliedToProperty(): void
    {
        $testClass = new ResultWithPropertyAttribute();

        $reflection = new \ReflectionProperty($testClass, 'id');
        $attributes = $reflection->getAttributes(ResultProperty::class);

        $this->assertCount(1, $attributes);
        $attr = $attributes[0]->newInstance();
        $this->assertSame('测试属性', $attr->description);
    }

    public function testAttributeCanBeAppliedToConstructorParameter(): void
    {
        $testClass = new ResultWithConstructorAttribute(id: 1, name: 'test');

        $reflection = new \ReflectionClass($testClass);
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);

        $params = $constructor->getParameters();
        $this->assertCount(2, $params);

        // 检查第一个参数
        $idAttrs = $params[0]->getAttributes(ResultProperty::class);
        $this->assertCount(1, $idAttrs);
        $idAttr = $idAttrs[0]->newInstance();
        $this->assertSame('ID字段', $idAttr->description);
        $this->assertFalse($idAttr->nullable);

        // 检查第二个参数
        $nameAttrs = $params[1]->getAttributes(ResultProperty::class);
        $this->assertCount(1, $nameAttrs);
        $nameAttr = $nameAttrs[0]->newInstance();
        $this->assertSame('名称字段', $nameAttr->description);
        $this->assertTrue($nameAttr->nullable);
    }
}
