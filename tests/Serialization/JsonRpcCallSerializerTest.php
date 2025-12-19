<?php

namespace Tourze\JsonRPC\Core\Tests\Serialization;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\JsonRpcInvalidRequestException;
use Tourze\JsonRPC\Core\Exception\JsonRpcParseErrorException;
use Tourze\JsonRPC\Core\Model\JsonRpcCallResponse;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;
use Tourze\JsonRPC\Core\Serialization\JsonRpcCallDenormalizer;
use Tourze\JsonRPC\Core\Serialization\JsonRpcCallResponseNormalizer;
use Tourze\JsonRPC\Core\Serialization\JsonRpcCallSerializer;
use Tourze\JsonRPC\Core\Serialization\JsonRpcRequestDenormalizer;
use Tourze\JsonRPC\Core\Serialization\JsonRpcResponseNormalizer;

/**
 * @internal
 */
#[CoversClass(JsonRpcCallSerializer::class)]
final class JsonRpcCallSerializerTest extends TestCase
{
    private JsonRpcCallSerializer $serializer;

    private JsonRpcCallDenormalizer $callDenormalizer;

    private JsonRpcCallResponseNormalizer $callResponseNormalizer;

    protected function setUp(): void
    {
        // 使用真实的依赖实例
        $requestDenormalizer = new JsonRpcRequestDenormalizer();
        $responseNormalizer = new JsonRpcResponseNormalizer();

        $this->callDenormalizer = new JsonRpcCallDenormalizer($requestDenormalizer);
        $this->callResponseNormalizer = new JsonRpcCallResponseNormalizer($responseNormalizer);

        // 创建 JsonRpcCallSerializer 实例
        $this->serializer = new JsonRpcCallSerializer($this->callDenormalizer, $this->callResponseNormalizer);
    }

    public function testDeserializeWithValidJsonReturnsCallRequest(): void
    {
        $content = '{"jsonrpc":"2.0","method":"test","id":1}';

        $result = $this->serializer->deserialize($content);

        $this->assertFalse($result->isBatch());
        $this->assertCount(1, $result->getItemList());

        $request = $result->getItemList()[0];
        $this->assertSame('2.0', $request->getJsonrpc());
        $this->assertSame('test', $request->getMethod());
        $this->assertSame(1, $request->getId());
    }

    public function testDeserializeWithInvalidJsonThrowsParseErrorException(): void
    {
        $content = '{invalid json}';

        $this->expectException(JsonRpcParseErrorException::class);
        $this->serializer->deserialize($content);
    }

    public function testDeserializeWithEmptyArrayThrowsInvalidRequestException(): void
    {
        $content = '[]';

        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->serializer->deserialize($content);
    }

    public function testDeserializeWithNonArrayContentThrowsInvalidRequestException(): void
    {
        $content = '"string"';

        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->serializer->deserialize($content);
    }

    public function testSerializeWithValidResponseReturnsJsonString(): void
    {
        // 使用真实的 JsonRpcResponse 和 JsonRpcCallResponse 实例
        $response = new JsonRpcResponse();
        $response->setId(1);
        $response->setResult('test');

        $callResponse = new JsonRpcCallResponse(false);
        $callResponse->addResponse($response);

        $result = $this->serializer->serialize($callResponse);

        $expectedJson = '{"jsonrpc":"2.0","id":1,"result":"test"}';
        $this->assertSame($expectedJson, $result);
    }

    public function testSerializeWithNullNormalizedDataReturnsNullJsonString(): void
    {
        // 使用真实的 JsonRpcCallResponse 实例，不添加任何响应
        $callResponse = new JsonRpcCallResponse(false);

        $result = $this->serializer->serialize($callResponse);

        $this->assertSame('null', $result);
    }

    public function testEncodeWithValidDataReturnsJsonString(): void
    {
        $data = ['test' => 'value'];
        $result = $this->serializer->encode($data);

        $this->assertSame('{"test":"value"}', $result);
    }

    public function testDecodeWithValidJsonReturnsDecodedArray(): void
    {
        $json = '{"test":"value"}';
        $result = $this->serializer->decode($json);

        $this->assertSame(['test' => 'value'], $result);
    }

    public function testDecodeWithInvalidJsonThrowsParseErrorException(): void
    {
        $json = '{invalid}';

        $this->expectException(JsonRpcParseErrorException::class);
        $this->serializer->decode($json);
    }

    public function testDecodeWithEmptyArrayThrowsInvalidRequestException(): void
    {
        $json = '[]';

        $this->expectException(JsonRpcInvalidRequestException::class);
        $this->serializer->decode($json);
    }

    public function testDenormalizeWithValidDataReturnsCallRequest(): void
    {
        $data = ['jsonrpc' => '2.0', 'method' => 'test', 'id' => 1];

        $result = $this->serializer->denormalize($data);

        $this->assertFalse($result->isBatch());
        $this->assertCount(1, $result->getItemList());

        $request = $result->getItemList()[0];
        $this->assertSame('2.0', $request->getJsonrpc());
        $this->assertSame('test', $request->getMethod());
        $this->assertSame(1, $request->getId());
    }

    public function testNormalizeWithValidResponseReturnsNormalizedData(): void
    {
        // 使用真实的 JsonRpcResponse 和 JsonRpcCallResponse 实例
        $response = new JsonRpcResponse();
        $response->setId(1);
        $response->setResult('test');

        $callResponse = new JsonRpcCallResponse(false);
        $callResponse->addResponse($response);

        $result = $this->serializer->normalize($callResponse);

        $this->assertIsArray($result);
        $this->assertSame('2.0', $result['jsonrpc']);
        $this->assertSame('test', $result['result']);
        $this->assertSame(1, $result['id']);
    }
}
