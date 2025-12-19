<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Result;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;
use Tourze\JsonRPC\Core\Helper\ResultObjectSerializer;
use Tourze\JsonRPC\Core\Result\ArrayResult;

#[CoversClass(ArrayResult::class)]
class ArrayResultTest extends TestCase
{
    public function testImplementsRpcResultInterface(): void
    {
        $result = new ArrayResult([]);
        $this->assertInstanceOf(RpcResultInterface::class, $result);
    }

    public function testEmptyArray(): void
    {
        $result = new ArrayResult([]);
        $this->assertSame([], $result->data);
        $this->assertCount(0, $result);
    }

    public function testWithData(): void
    {
        $data = ['id' => 1, 'name' => 'Test'];
        $result = new ArrayResult($data);
        $this->assertSame($data, $result->data);
        $this->assertCount(2, $result);
    }

    public function testArrayAccess(): void
    {
        $data = ['id' => 1, 'name' => 'Test'];
        $result = new ArrayResult($data);

        $this->assertTrue(isset($result['id']));
        $this->assertFalse(isset($result['nonexistent']));
        $this->assertSame(1, $result['id']);
        $this->assertSame('Test', $result['name']);
        $this->assertNull($result['nonexistent']);
    }

    public function testIterator(): void
    {
        $data = ['id' => 1, 'name' => 'Test'];
        $result = new ArrayResult($data);

        $iterated = [];
        foreach ($result as $key => $value) {
            $iterated[$key] = $value;
        }

        $this->assertSame($data, $iterated);
    }

    public function testJsonSerialize(): void
    {
        $data = ['id' => 1, 'name' => 'Test'];
        $result = new ArrayResult($data);

        $this->assertSame($data, $result->jsonSerialize());
        $this->assertSame(json_encode($data), json_encode($result));
    }

    public function testToArray(): void
    {
        $data = ['id' => 1, 'name' => 'Test'];
        $result = new ArrayResult($data);

        $this->assertSame($data, $result->toArray());
    }

    public function testSerializationWithResultObjectSerializer(): void
    {
        $data = [
            'id' => 1,
            'name' => 'Test',
            'nested' => ['a' => 1, 'b' => 2],
            'list' => [1, 2, 3],
        ];
        $result = new ArrayResult($data);

        $serializer = new ResultObjectSerializer();
        $serialized = $serializer->serialize($result);

        $this->assertSame($data, $serialized);
    }

    public function testReadonlyBehavior(): void
    {
        $result = new ArrayResult(['id' => 1]);

        // These operations should do nothing on readonly class
        $result['id'] = 2;
        unset($result['id']);

        // Value should remain unchanged
        $this->assertSame(1, $result['id']);
    }

    public function testNestedArrayData(): void
    {
        $data = [
            'list' => [
                ['id' => 1, 'name' => 'Item 1'],
                ['id' => 2, 'name' => 'Item 2'],
            ],
            'pagination' => [
                'current' => 1,
                'pageSize' => 10,
                'total' => 100,
            ],
        ];
        $result = new ArrayResult($data);

        $serializer = new ResultObjectSerializer();
        $serialized = $serializer->serialize($result);

        $this->assertSame($data, $serialized);
    }

    public function testCount(): void
    {
        $emptyResult = new ArrayResult([]);
        $this->assertSame(0, $emptyResult->count());

        $result = new ArrayResult(['id' => 1, 'name' => 'Test', 'value' => 123]);
        $this->assertSame(3, $result->count());
    }

    public function testOffsetExists(): void
    {
        $result = new ArrayResult(['id' => 1, 'name' => 'Test']);

        $this->assertTrue($result->offsetExists('id'));
        $this->assertTrue($result->offsetExists('name'));
        $this->assertFalse($result->offsetExists('nonexistent'));
        $this->assertFalse($result->offsetExists('unknown'));
    }

    public function testOffsetGet(): void
    {
        $result = new ArrayResult(['id' => 1, 'name' => 'Test', 'nullable' => null]);

        $this->assertSame(1, $result->offsetGet('id'));
        $this->assertSame('Test', $result->offsetGet('name'));
        $this->assertNull($result->offsetGet('nullable'));
        $this->assertNull($result->offsetGet('nonexistent'));
    }

    public function testOffsetSet(): void
    {
        $result = new ArrayResult(['id' => 1]);

        // readonly class should not allow modification
        $result->offsetSet('id', 999);
        $result->offsetSet('newKey', 'newValue');

        // Values should remain unchanged
        $this->assertSame(1, $result->offsetGet('id'));
        $this->assertNull($result->offsetGet('newKey'));
    }

    public function testOffsetUnset(): void
    {
        $result = new ArrayResult(['id' => 1, 'name' => 'Test']);

        // readonly class should not allow unsetting
        $result->offsetUnset('id');
        $result->offsetUnset('name');

        // Values should remain unchanged
        $this->assertTrue($result->offsetExists('id'));
        $this->assertTrue($result->offsetExists('name'));
        $this->assertSame(1, $result->offsetGet('id'));
        $this->assertSame('Test', $result->offsetGet('name'));
    }
}
