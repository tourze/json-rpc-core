<?php

namespace Tourze\JsonRPC\Core\Tests\Serialization;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\JsonRpcInvalidRequestException;
use Tourze\JsonRPC\Core\Model\JsonRpcCallRequest;
use Tourze\JsonRPC\Core\Serialization\JsonRpcCallDenormalizer;
use Tourze\JsonRPC\Core\Serialization\JsonRpcRequestDenormalizer;

/**
 * @internal
 */
#[CoversClass(JsonRpcCallDenormalizer::class)]
final class JsonRpcCallDenormalizerTest extends TestCase
{
    private JsonRpcCallDenormalizer $denormalizer;

    private JsonRpcRequestDenormalizer $requestDenormalizer;

    protected function setUp(): void
    {
        // 使用真实的 JsonRpcRequestDenormalizer 实例
        $this->requestDenormalizer = new JsonRpcRequestDenormalizer();

        // 创建 JsonRpcCallDenormalizer 实例
        $this->denormalizer = new JsonRpcCallDenormalizer($this->requestDenormalizer);
    }

    public function testDenormalizeWithSingleRequestReturnsNonBatchCall(): void
    {
        $decodedContent = ['jsonrpc' => '2.0', 'method' => 'test', 'id' => 1];

        $result = $this->denormalizer->denormalize($decodedContent);

        $this->assertInstanceOf(JsonRpcCallRequest::class, $result);
        $this->assertFalse($result->isBatch());
        $this->assertCount(1, $result->getItemList());

        $jsonRpcRequest = $result->getItemList()[0];
        $this->assertSame('2.0', $jsonRpcRequest->getJsonrpc());
        $this->assertSame('test', $jsonRpcRequest->getMethod());
        $this->assertSame(1, $jsonRpcRequest->getId());
    }

    public function testDenormalizeWithBatchRequestReturnsBatchCall(): void
    {
        $decodedContent = [
            ['jsonrpc' => '2.0', 'method' => 'test1', 'id' => 1],
            ['jsonrpc' => '2.0', 'method' => 'test2', 'id' => 2],
        ];

        $result = $this->denormalizer->denormalize($decodedContent);

        $this->assertInstanceOf(JsonRpcCallRequest::class, $result);
        $this->assertTrue($result->isBatch());
        $this->assertCount(2, $result->getItemList());

        $jsonRpcRequest1 = $result->getItemList()[0];
        $this->assertSame('2.0', $jsonRpcRequest1->getJsonrpc());
        $this->assertSame('test1', $jsonRpcRequest1->getMethod());
        $this->assertSame(1, $jsonRpcRequest1->getId());

        $jsonRpcRequest2 = $result->getItemList()[1];
        $this->assertSame('2.0', $jsonRpcRequest2->getJsonrpc());
        $this->assertSame('test2', $jsonRpcRequest2->getMethod());
        $this->assertSame(2, $jsonRpcRequest2->getId());
    }

    public function testDenormalizeWithEmptyArrayCallsDenormalizerWithEmptyArray(): void
    {
        $data = [];

        // 空数组会被当作非批量请求处理，传递给 requestDenormalizer 时会触发异常
        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->expectExceptionMessage('"jsonrpc" is a required key');

        $this->denormalizer->denormalize($data);
    }

    public function testDenormalizeWithSingleRequestExceptionThrowsException(): void
    {
        // 传入缺少必需字段的数据，触发真实的异常
        $decodedContent = ['method' => 'test', 'id' => 1]; // 缺少 jsonrpc 字段

        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->expectExceptionMessage('"jsonrpc" is a required key');

        $this->denormalizer->denormalize($decodedContent);
    }

    public function testDenormalizeWithBatchRequestExceptionAddsExceptionToCall(): void
    {
        // 第一个请求有效，第二个请求缺少必需字段会触发异常
        $decodedContent = [
            ['jsonrpc' => '2.0', 'method' => 'test1', 'id' => 1],
            ['method' => 'test2', 'id' => 2], // 缺少 jsonrpc 字段
        ];

        $result = $this->denormalizer->denormalize($decodedContent);

        $this->assertInstanceOf(JsonRpcCallRequest::class, $result);
        $this->assertTrue($result->isBatch());
        $this->assertCount(2, $result->getItemList());

        // 第一个应该是有效的 JsonRpcRequest
        $jsonRpcRequest1 = $result->getItemList()[0];
        $this->assertSame('2.0', $jsonRpcRequest1->getJsonrpc());
        $this->assertSame('test1', $jsonRpcRequest1->getMethod());
        $this->assertSame(1, $jsonRpcRequest1->getId());

        // 第二个应该是异常
        $exception = $result->getItemList()[1];
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertStringContainsString('"jsonrpc" is a required key', $exception->getMessage());
    }

    public function testGuessBatchOrNotWithNumericKeysReturnsTrue(): void
    {
        $decodedContent = [
            0 => ['jsonrpc' => '2.0', 'method' => 'test1', 'id' => 1],
            1 => ['jsonrpc' => '2.0', 'method' => 'test2', 'id' => 2],
        ];

        // 使用反射调用私有方法
        $method = new \ReflectionMethod(JsonRpcCallDenormalizer::class, 'guessBatchOrNot');
        $method->setAccessible(true);
        $result = $method->invoke(null, $decodedContent);

        $this->assertTrue($result);
    }

    public function testGuessBatchOrNotWithStringKeysReturnsFalse(): void
    {
        $decodedContent = ['jsonrpc' => '2.0', 'method' => 'test', 'id' => 1];

        // 使用反射调用私有方法
        $method = new \ReflectionMethod(JsonRpcCallDenormalizer::class, 'guessBatchOrNot');
        $method->setAccessible(true);
        $result = $method->invoke(null, $decodedContent);

        $this->assertFalse($result);
    }

    public function testGuessBatchOrNotWithEmptyArrayReturnsFalse(): void
    {
        $decodedContent = [];

        // 使用反射调用私有方法
        $method = new \ReflectionMethod(JsonRpcCallDenormalizer::class, 'guessBatchOrNot');
        $method->setAccessible(true);
        $result = $method->invoke(null, $decodedContent);

        $this->assertFalse($result);
    }

    public function testGuessBatchOrNotWithMixedKeysReturnsFalse(): void
    {
        $decodedContent = [
            0 => ['jsonrpc' => '2.0', 'method' => 'test1', 'id' => 1],
            'method' => 'test',
        ];

        // 使用反射调用私有方法
        $method = new \ReflectionMethod(JsonRpcCallDenormalizer::class, 'guessBatchOrNot');
        $method->setAccessible(true);
        $result = $method->invoke(null, $decodedContent);

        $this->assertFalse($result);
    }
}
