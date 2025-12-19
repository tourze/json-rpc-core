<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Serialization;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Helper\ResultObjectSerializer;
use Tourze\JsonRPC\Core\Result\EmptyResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\AllNullPropertiesResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\CircularReferenceResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\DateTimePropertyResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\EmptyArrayResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\EnumPropertyResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\LargeNumbersResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\SpecialCharactersResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\TestStatusEnum;

/**
 * 序列化边缘情况测试（null、循环引用、resource）
 */
#[CoversClass(ResultObjectSerializer::class)]
class ResultSerializationEdgeCaseTest extends TestCase
{
    private ResultObjectSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new ResultObjectSerializer();
    }

    public function testSerializeResultWithAllNullProperties(): void
    {
        $result = new AllNullPropertiesResult(a: null, b: null, c: null);

        $serialized = $this->serializer->serialize($result);

        $this->assertNull($serialized['a']);
        $this->assertNull($serialized['b']);
        $this->assertNull($serialized['c']);
    }

    public function testSerializeEmptyArrayProperty(): void
    {
        $result = new EmptyArrayResult(items: []);

        $serialized = $this->serializer->serialize($result);

        $this->assertIsArray($serialized['items']);
        $this->assertEmpty($serialized['items']);
    }

    public function testSerializeResultWithSpecialCharacters(): void
    {
        $result = new SpecialCharactersResult(
            unicodeStr: '中文测试 🎉 émojis',
            htmlStr: '<script>alert("xss")</script>',
            newlineStr: "line1\nline2\tline3",
        );

        $serialized = $this->serializer->serialize($result);

        $this->assertSame('中文测试 🎉 émojis', $serialized['unicodeStr']);
        $this->assertSame('<script>alert("xss")</script>', $serialized['htmlStr']);
        $this->assertSame("line1\nline2\tline3", $serialized['newlineStr']);
    }

    public function testSerializeResultWithLargeNumbers(): void
    {
        $result = new LargeNumbersResult(
            largeInt: PHP_INT_MAX,
            largeFloat: PHP_FLOAT_MAX,
            negativeInt: PHP_INT_MIN,
        );

        $serialized = $this->serializer->serialize($result);

        $this->assertSame(PHP_INT_MAX, $serialized['largeInt']);
        $this->assertSame(PHP_FLOAT_MAX, $serialized['largeFloat']);
        $this->assertSame(PHP_INT_MIN, $serialized['negativeInt']);
    }

    public function testSerializeEmptyResult(): void
    {
        $result = new EmptyResult();
        $serialized = $this->serializer->serialize($result);

        $this->assertIsArray($serialized);
        $this->assertEmpty($serialized);
    }

    public function testSerializeResultWithDateTimeProperty(): void
    {
        $dateTime = new \DateTimeImmutable('2025-01-15 10:30:00');
        $result = new DateTimePropertyResult(createdAt: $dateTime);

        $serialized = $this->serializer->serialize($result);

        // DateTime 应该被序列化为 ISO 8601 字符串
        $this->assertIsString($serialized['createdAt']);
        $this->assertStringContainsString('2025-01-15', $serialized['createdAt']);
    }

    public function testSerializeResultWithEnumProperty(): void
    {
        $result = new EnumPropertyResult(status: TestStatusEnum::ACTIVE);

        $serialized = $this->serializer->serialize($result);

        // Backed enum 应该序列化为其值
        $this->assertSame('active', $serialized['status']);
    }

    public function testCircularReferenceThrowsException(): void
    {
        // 创建一个可以自引用的类来测试循环引用
        $result = new CircularReferenceResult();
        $result->self = $result; // 创建循环引用

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('循环引用');

        $this->serializer->serialize($result);
    }
}
