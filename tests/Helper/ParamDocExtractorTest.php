<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Helper;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Helper\ParamDocExtractor;
use Tourze\JsonRPC\Core\Tests\Fixtures\DocExtractorArrayParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\DocExtractorNoMethodParamParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\DocExtractorSimpleParam;

#[CoversClass(ParamDocExtractor::class)]
final class ParamDocExtractorTest extends TestCase
{
    public function testExtractReturnsEmptyArrayForNonParamClass(): void
    {
        $result = ParamDocExtractor::extract(\stdClass::class);
        $this->assertSame([], $result);
    }

    public function testExtractReturnsParameterInfo(): void
    {
        $result = ParamDocExtractor::extract(DocExtractorSimpleParam::class);

        $this->assertCount(4, $result);
        $this->assertArrayHasKey('userId', $result);
        $this->assertArrayHasKey('username', $result);
        $this->assertArrayHasKey('age', $result);
        $this->assertArrayHasKey('email', $result);
    }

    public function testExtractParameterName(): void
    {
        $result = ParamDocExtractor::extract(DocExtractorSimpleParam::class);

        $this->assertSame('userId', $result['userId']['name']);
        $this->assertSame('username', $result['username']['name']);
    }

    public function testExtractParameterType(): void
    {
        $result = ParamDocExtractor::extract(DocExtractorSimpleParam::class);

        $this->assertSame('string', $result['userId']['type']);
        $this->assertSame('?string', $result['username']['type']);
        $this->assertSame('int', $result['age']['type']);
        $this->assertSame('string', $result['email']['type']);
    }

    public function testExtractParameterDescription(): void
    {
        $result = ParamDocExtractor::extract(DocExtractorSimpleParam::class);

        $this->assertSame('用户ID', $result['userId']['description']);
        $this->assertSame('用户名', $result['username']['description']);
        $this->assertSame('年龄', $result['age']['description']);
        $this->assertSame('邮箱', $result['email']['description']);
    }

    public function testExtractParameterRequired(): void
    {
        $result = ParamDocExtractor::extract(DocExtractorSimpleParam::class);

        // userId 是必填的（无默认值）
        $this->assertTrue($result['userId']['required']);
        // username 是可选的（有默认值 null 且 optional=true）
        $this->assertFalse($result['username']['required']);
        // age 是可选的（有默认值）
        $this->assertFalse($result['age']['required']);
        // email 是可选的（有默认值）
        $this->assertFalse($result['email']['required']);
    }

    public function testExtractParameterDefaultValue(): void
    {
        $result = ParamDocExtractor::extract(DocExtractorSimpleParam::class);

        // userId 无默认值
        $this->assertNull($result['userId']['default']);
        // username 默认值为 null
        $this->assertNull($result['username']['default']);
        // age 默认值为 18
        $this->assertSame(18, $result['age']['default']);
        // email 默认值
        $this->assertSame('default@example.com', $result['email']['default']);
    }

    public function testExtractValidationConstraints(): void
    {
        $result = ParamDocExtractor::extract(DocExtractorSimpleParam::class);

        // userId 有 NotBlank 约束
        $this->assertNotEmpty($result['userId']['constraints']);
        $this->assertContains('NotBlank', $result['userId']['constraints']);

        // age 有 Range 约束
        $this->assertNotEmpty($result['age']['constraints']);
        $this->assertContains('Range', $result['age']['constraints']);

        // email 有 Email 约束
        $this->assertNotEmpty($result['email']['constraints']);
        $this->assertContains('Email', $result['email']['constraints']);
    }

    public function testExtractWithoutMethodParamAttribute(): void
    {
        $result = ParamDocExtractor::extract(DocExtractorNoMethodParamParam::class);

        $this->assertCount(2, $result);
        // 无 MethodParam 属性时，description 应为空
        $this->assertSame('', $result['id']['description']);
        $this->assertSame('', $result['count']['description']);
    }

    public function testExtractArrayTypes(): void
    {
        $result = ParamDocExtractor::extract(DocExtractorArrayParam::class);

        $this->assertSame('array', $result['tags']['type']);
        $this->assertSame('array', $result['counts']['type']);
    }

    public function testExtractSupportsMethod(): void
    {
        $this->assertTrue(ParamDocExtractor::supports(DocExtractorSimpleParam::class));
        $this->assertTrue(ParamDocExtractor::supports(DocExtractorNoMethodParamParam::class));
        $this->assertFalse(ParamDocExtractor::supports(\stdClass::class));
        $this->assertFalse(ParamDocExtractor::supports('NonExistentClass'));
    }
}
