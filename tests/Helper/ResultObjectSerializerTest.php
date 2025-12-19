<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Helper;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Helper\ResultObjectSerializer;
use Tourze\JsonRPC\Core\Result\EmptyResult;
use Tourze\JsonRPC\Core\Result\SuccessResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\AllBasicTypesResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\ArrayPropertyResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\CustomResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\NullablePropertyResult;

/**
 * ResultObjectSerializer 单元测试
 */
#[CoversClass(ResultObjectSerializer::class)]
class ResultObjectSerializerTest extends TestCase
{
    private ResultObjectSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new ResultObjectSerializer();
    }

    public function testSerializeEmptyResult(): void
    {
        $result = new EmptyResult();
        $serialized = $this->serializer->serialize($result);

        $this->assertIsArray($serialized);
        $this->assertEmpty($serialized);
    }

    public function testSerializeSuccessResult(): void
    {
        $result = new SuccessResult(success: true, message: '操作成功');
        $serialized = $this->serializer->serialize($result);

        $this->assertIsArray($serialized);
        $this->assertTrue($serialized['success']);
        $this->assertSame('操作成功', $serialized['message']);
    }

    public function testSerializeSuccessResultWithNullMessage(): void
    {
        $result = new SuccessResult(success: true, message: null);
        $serialized = $this->serializer->serialize($result);

        $this->assertIsArray($serialized);
        $this->assertTrue($serialized['success']);
        $this->assertNull($serialized['message']);
    }

    public function testSerializeCustomResult(): void
    {
        $result = new CustomResult(id: 123, name: 'Test');

        $serialized = $this->serializer->serialize($result);

        $this->assertIsArray($serialized);
        $this->assertSame(123, $serialized['id']);
        $this->assertSame('Test', $serialized['name']);
    }

    public function testSerializeResultWithNullableProperty(): void
    {
        $result = new NullablePropertyResult(id: 1, email: null);

        $serialized = $this->serializer->serialize($result);

        $this->assertIsArray($serialized);
        $this->assertSame(1, $serialized['id']);
        $this->assertNull($serialized['email']);
    }

    public function testSerializeResultWithAllBasicTypes(): void
    {
        $result = new AllBasicTypesResult(
            stringVal: 'hello',
            intVal: 42,
            floatVal: 3.14,
            boolVal: true,
            nullVal: null,
        );

        $serialized = $this->serializer->serialize($result);

        $this->assertSame('hello', $serialized['stringVal']);
        $this->assertSame(42, $serialized['intVal']);
        $this->assertSame(3.14, $serialized['floatVal']);
        $this->assertTrue($serialized['boolVal']);
        $this->assertNull($serialized['nullVal']);
    }

    public function testSerializeResultWithArray(): void
    {
        $result = new ArrayPropertyResult(items: ['a', 'b', 'c']);

        $serialized = $this->serializer->serialize($result);

        $this->assertIsArray($serialized['items']);
        $this->assertSame(['a', 'b', 'c'], $serialized['items']);
    }
}
