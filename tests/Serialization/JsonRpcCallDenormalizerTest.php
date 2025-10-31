<?php

namespace Tourze\JsonRPC\Core\Tests\Serialization;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\JsonRpcInvalidRequestException;
use Tourze\JsonRPC\Core\Model\JsonRpcCallRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Serialization\JsonRpcCallDenormalizer;
use Tourze\JsonRPC\Core\Serialization\JsonRpcRequestDenormalizer;

/**
 * @internal
 */
#[CoversClass(JsonRpcCallDenormalizer::class)]
final class JsonRpcCallDenormalizerTest extends TestCase
{
    private JsonRpcCallDenormalizer $denormalizer;

    private JsonRpcRequestDenormalizer&MockObject $requestDenormalizer;

    protected function setUp(): void
    {
        // 创建mock对象
        $this->requestDenormalizer = $this->createMock(JsonRpcRequestDenormalizer::class);

        // 直接创建JsonRpcCallDenormalizer实例（使用mock依赖）
        $this->denormalizer = new JsonRpcCallDenormalizer($this->requestDenormalizer);
    }

    public function testDenormalizeWithSingleRequestReturnsNonBatchCall(): void
    {
        $decodedContent = ['jsonrpc' => '2.0', 'method' => 'test', 'id' => 1];
        $jsonRpcRequest = new JsonRpcRequest();

        $this->requestDenormalizer->expects($this->once())
            ->method('denormalize')
            ->with($decodedContent)
            ->willReturn($jsonRpcRequest)
        ;

        $result = $this->denormalizer->denormalize($decodedContent);

        $this->assertInstanceOf(JsonRpcCallRequest::class, $result);
        $this->assertFalse($result->isBatch());
        $this->assertCount(1, $result->getItemList());
        $this->assertSame($jsonRpcRequest, $result->getItemList()[0]);
    }

    public function testDenormalizeWithBatchRequestReturnsBatchCall(): void
    {
        $decodedContent = [
            ['jsonrpc' => '2.0', 'method' => 'test1', 'id' => 1],
            ['jsonrpc' => '2.0', 'method' => 'test2', 'id' => 2],
        ];
        $jsonRpcRequest1 = new JsonRpcRequest();
        $jsonRpcRequest2 = new JsonRpcRequest();

        $this->requestDenormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnOnConsecutiveCalls($jsonRpcRequest1, $jsonRpcRequest2)
        ;

        $result = $this->denormalizer->denormalize($decodedContent);

        $this->assertInstanceOf(JsonRpcCallRequest::class, $result);
        $this->assertTrue($result->isBatch());
        $this->assertCount(2, $result->getItemList());
        $this->assertSame($jsonRpcRequest1, $result->getItemList()[0]);
        $this->assertSame($jsonRpcRequest2, $result->getItemList()[1]);
    }

    public function testDenormalizeWithEmptyArrayCallsDenormalizerWithEmptyArray(): void
    {
        $data = [];

        // Mock期望会被调用一次，传入空数组
        $this->requestDenormalizer->expects($this->once())
            ->method('denormalize')
            ->with([])
            ->willThrowException(new JsonRpcInvalidRequestException([], '"jsonrpc" is a required key'))
        ;

        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->expectExceptionMessage('"jsonrpc" is a required key');

        $this->denormalizer->denormalize($data);
    }

    public function testDenormalizeWithSingleRequestExceptionThrowsException(): void
    {
        $decodedContent = ['jsonrpc' => '2.0', 'method' => 'test', 'id' => 1];
        $exception = new \Exception('Test exception');

        $this->requestDenormalizer->expects($this->once())
            ->method('denormalize')
            ->with($decodedContent)
            ->willThrowException($exception)
        ;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test exception');

        $this->denormalizer->denormalize($decodedContent);
    }

    public function testDenormalizeWithBatchRequestExceptionAddsExceptionToCall(): void
    {
        $decodedContent = [
            ['jsonrpc' => '2.0', 'method' => 'test1', 'id' => 1],
            ['jsonrpc' => '2.0', 'method' => 'test2', 'id' => 2],
        ];
        $jsonRpcRequest1 = new JsonRpcRequest();
        $exception = new \Exception('Test exception');

        $this->requestDenormalizer->expects($this->exactly(2))
            ->method('denormalize')
            ->willReturnCallback(function ($item) use ($jsonRpcRequest1, $exception) {
                $this->assertIsArray($item);
                if (1 === $item['id']) {
                    return $jsonRpcRequest1;
                }
                throw $exception;
            })
        ;

        $result = $this->denormalizer->denormalize($decodedContent);

        $this->assertInstanceOf(JsonRpcCallRequest::class, $result);
        $this->assertTrue($result->isBatch());
        $this->assertCount(2, $result->getItemList());
        $this->assertSame($jsonRpcRequest1, $result->getItemList()[0]);
        $this->assertSame($exception, $result->getItemList()[1]);
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
