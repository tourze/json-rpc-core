<?php

namespace Tourze\JsonRPC\Core\Tests\Serialization;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\JsonRpcInvalidRequestException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Serialization\JsonRpcRequestDenormalizer;

/**
 * @internal
 */
#[CoversClass(JsonRpcRequestDenormalizer::class)]
final class JsonRpcRequestDenormalizerTest extends TestCase
{
    private JsonRpcRequestDenormalizer $denormalizer;

    protected function setUp(): void
    {
        $this->denormalizer = new JsonRpcRequestDenormalizer();
    }

    public function testDenormalizeWithValidRequestReturnsJsonRpcRequest(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'params' => ['param1' => 'value1'],
            'id' => 1,
        ];

        $result = $this->denormalizer->denormalize($data);

        $this->assertInstanceOf(JsonRpcRequest::class, $result);
        $this->assertEquals('2.0', $result->getJsonrpc());
        $this->assertEquals('test.method', $result->getMethod());

        $params = $result->getParams();
        $this->assertNotNull($params);
        $this->assertEquals(['param1' => 'value1'], $params->toArray());
        // ID 会被转换为整数，但 getId() 返回实际存储的类型
        $this->assertEquals(1, $result->getId());
    }

    public function testDenormalizeWithStringIdPreservesStringType(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'params' => [],
            'id' => 'string-id',
        ];

        $result = $this->denormalizer->denormalize($data);

        $this->assertEquals('string-id', $result->getId());
    }

    public function testDenormalizeWithNumericStringIdConvertsToInt(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'params' => [],
            'id' => '123',
        ];

        $result = $this->denormalizer->denormalize($data);

        // 数字字符串会被转换为整数
        $this->assertEquals(123, $result->getId());
    }

    public function testDenormalizeWithoutIdSetsNullId(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'params' => [],
        ];

        $result = $this->denormalizer->denormalize($data);

        $this->assertNull($result->getId());
        $this->assertTrue($result->isNotification());
    }

    public function testDenormalizeWithoutParamsSetsEmptyParams(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'id' => 1,
        ];

        $result = $this->denormalizer->denormalize($data);

        $params = $result->getParams();
        $this->assertNotNull($params);
        $this->assertEquals([], $params->toArray());
    }

    public function testDenormalizeWithMissingJsonrpcThrowsException(): void
    {
        $data = [
            'method' => 'test.method',
            'id' => 1,
        ];

        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->expectExceptionMessage('"jsonrpc" is a required key');

        $this->denormalizer->denormalize($data);
    }

    public function testDenormalizeWithMissingMethodThrowsException(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'id' => 1,
        ];

        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->expectExceptionMessage('"method" is a required key');

        $this->denormalizer->denormalize($data);
    }

    public function testDenormalizeWithZeroIdBindsCorrectly(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'params' => [],
            'id' => 0,
        ];

        $result = $this->denormalizer->denormalize($data);

        $this->assertEquals(0, $result->getId());
        $this->assertFalse($result->isNotification());
    }

    public function testDenormalizeWithNullIdTreatsAsNotification(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'params' => [],
            'id' => null,
        ];

        $result = $this->denormalizer->denormalize($data);

        $this->assertNull($result->getId());
        $this->assertTrue($result->isNotification());
    }

    public function testDenormalizeWithNonArrayDataThrowsException(): void
    {
        $data = 'not an array';

        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->expectExceptionMessage('Item must be an array');

        $this->denormalizer->denormalize($data);
    }

    public function testDenormalizeWithNonArrayParamsThrowsException(): void
    {
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'id' => 1,
            'params' => 'not an array',
        ];

        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->expectExceptionMessage('Parameter list must be an array');

        $this->denormalizer->denormalize($data);
    }

    public function testDenormalizeWithComplexParamsBindsCorrectly(): void
    {
        $params = [
            'user' => ['name' => 'John', 'age' => 30],
            'data' => [1, 2, 3],
            'config' => ['debug' => true],
        ];
        $data = [
            'jsonrpc' => '2.0',
            'method' => 'test.method',
            'id' => 1,
            'params' => $params,
        ];

        $result = $this->denormalizer->denormalize($data);

        $resultParams = $result->getParams();
        $this->assertNotNull($resultParams);
        $this->assertEquals($params, $resultParams->toArray());
    }
}
