<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Model;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;

class JsonRpcParamsTest extends TestCase
{
    public function testJsonRpcParamsCreation(): void
    {
        $params = new JsonRpcParams([
            'foo' => 'bar',
            'baz' => 123,
        ]);

        $this->assertEquals('bar', $params->get('foo'));
        $this->assertEquals(123, $params->get('baz'));
    }

    public function testToArray(): void
    {
        $paramsData = [
            'foo' => 'bar',
            'baz' => [1, 2, 3],
            'complex' => ['nested' => true],
        ];

        $params = new JsonRpcParams($paramsData);

        $this->assertEquals($paramsData, $params->toArray());
        $this->assertEquals($paramsData, $params->all());
    }

    public function testAddParameter(): void
    {
        $params = new JsonRpcParams();

        $params->set('key1', 'value1');
        $params->set('key2', ['a', 'b', 'c']);

        $this->assertEquals('value1', $params->get('key1'));
        $this->assertEquals(['a', 'b', 'c'], $params->get('key2'));
    }

    public function testRemoveParameter(): void
    {
        $params = new JsonRpcParams([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->assertTrue($params->has('key1'));

        $params->remove('key1');

        $this->assertFalse($params->has('key1'));
        $this->assertTrue($params->has('key2'));
    }

    public function testClearParameters(): void
    {
        $params = new JsonRpcParams([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);

        $this->assertCount(2, $params->all());

        $params->replace([]);

        $this->assertCount(0, $params->all());
        $this->assertEquals([], $params->toArray());
    }
}
