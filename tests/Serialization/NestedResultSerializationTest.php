<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Serialization;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Helper\ResultObjectSerializer;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\InnerResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\Level1Result;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\Level2Result;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\Level3Result;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\ListResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\MixedArrayResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\OuterResult;

/**
 * 嵌套 Result 序列化测试
 */
#[CoversClass(ResultObjectSerializer::class)]
class NestedResultSerializationTest extends TestCase
{
    private ResultObjectSerializer $serializer;

    protected function setUp(): void
    {
        $this->serializer = new ResultObjectSerializer();
    }

    public function testSerializeNestedResult(): void
    {
        $innerResult = new InnerResult(id: 1, name: 'Inner');
        $outerResult = new OuterResult(title: 'Outer', nested: $innerResult);

        $serialized = $this->serializer->serialize($outerResult);

        $this->assertIsArray($serialized);
        $this->assertSame('Outer', $serialized['title']);
        $this->assertIsArray($serialized['nested']);
        $this->assertSame(1, $serialized['nested']['id']);
        $this->assertSame('Inner', $serialized['nested']['name']);
    }

    public function testSerializeResultWithResultArray(): void
    {
        $item1 = new InnerResult(id: 1, name: 'Item 1');
        $item2 = new InnerResult(id: 2, name: 'Item 2');
        $listResult = new ListResult(items: [$item1, $item2], total: 2);

        $serialized = $this->serializer->serialize($listResult);

        $this->assertIsArray($serialized);
        $this->assertSame(2, $serialized['total']);
        $this->assertIsArray($serialized['items']);
        $this->assertCount(2, $serialized['items']);
        $this->assertSame(1, $serialized['items'][0]['id']);
        $this->assertSame('Item 1', $serialized['items'][0]['name']);
        $this->assertSame(2, $serialized['items'][1]['id']);
        $this->assertSame('Item 2', $serialized['items'][1]['name']);
    }

    public function testSerializeDeeplyNestedResult(): void
    {
        $level3 = new Level3Result(value: 'deepest');
        $level2 = new Level2Result(level3: $level3);
        $level1 = new Level1Result(level2: $level2);

        $serialized = $this->serializer->serialize($level1);

        $this->assertIsArray($serialized);
        $this->assertIsArray($serialized['level2']);
        $this->assertIsArray($serialized['level2']['level3']);
        $this->assertSame('deepest', $serialized['level2']['level3']['value']);
    }

    public function testSerializeMixedArrayWithResults(): void
    {
        $result = new MixedArrayResult(
            data: [
                'string' => 'value',
                'number' => 42,
                'nested' => ['a' => 1, 'b' => 2],
            ]
        );

        $serialized = $this->serializer->serialize($result);

        $this->assertIsArray($serialized['data']);
        $this->assertSame('value', $serialized['data']['string']);
        $this->assertSame(42, $serialized['data']['number']);
        $this->assertSame(['a' => 1, 'b' => 2], $serialized['data']['nested']);
    }
}
