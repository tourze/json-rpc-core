<?php

namespace Tourze\JsonRPC\Core\Tests\Serialization;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\JsonRpcInvalidRequestException;
use Tourze\JsonRPC\Core\Exception\JsonRpcParseErrorException;
use Tourze\JsonRPC\Core\Model\JsonRpcCallRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcCallResponse;
use Tourze\JsonRPC\Core\Serialization\JsonRpcCallDenormalizer;
use Tourze\JsonRPC\Core\Serialization\JsonRpcCallResponseNormalizer;
use Tourze\JsonRPC\Core\Serialization\JsonRpcCallSerializer;

/**
 * @internal
 */
#[CoversClass(JsonRpcCallSerializer::class)]
final class JsonRpcCallSerializerTest extends TestCase
{
    private JsonRpcCallSerializer $serializer;

    private MockObject&JsonRpcCallDenormalizer $callDenormalizer;

    private MockObject&JsonRpcCallResponseNormalizer $callResponseNormalizer;

    protected function setUp(): void
    {
        // 创建mock对象
        $this->callDenormalizer = $this->createMock(JsonRpcCallDenormalizer::class);
        $this->callResponseNormalizer = $this->createMock(JsonRpcCallResponseNormalizer::class);

        // 直接创建JsonRpcCallSerializer实例（使用mock依赖）
        $this->serializer = new JsonRpcCallSerializer($this->callDenormalizer, $this->callResponseNormalizer);
    }

    public function testDeserializeWithValidJsonReturnsCallRequest(): void
    {
        $content = '{"jsonrpc":"2.0","method":"test","id":1}';
        $decodedContent = ['jsonrpc' => '2.0', 'method' => 'test', 'id' => 1];
        $callRequest = new JsonRpcCallRequest();

        $this->callDenormalizer->expects($this->once())
            ->method('denormalize')
            ->with($decodedContent)
            ->willReturn($callRequest)
        ;

        $result = $this->serializer->deserialize($content);

        $this->assertSame($callRequest, $result);
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
        // 使用JsonRpcCallResponse具体类模拟的原因：
        // 1) 它是数据传输对象(DTO)，不需要抽象接口
        // 2) 测试需要验证其具体的数据结构和行为
        // 3) 它是标准协议的实现，创建接口没有实际意义
        $callResponse = $this->createMock(JsonRpcCallResponse::class);
        $normalizedData = ['jsonrpc' => '2.0', 'result' => 'test', 'id' => 1];

        $this->callResponseNormalizer->expects($this->once())
            ->method('normalize')
            ->with($callResponse)
            ->willReturn($normalizedData)
        ;

        $result = $this->serializer->serialize($callResponse);

        $expectedJson = json_encode($normalizedData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $this->assertSame($expectedJson, $result);
    }

    public function testSerializeWithNullNormalizedDataReturnsNullJsonString(): void
    {
        // 使用JsonRpcCallResponse具体类模拟的原因：
        // 1) 它是数据传输对象(DTO)，不需要抽象接口
        // 2) 测试需要验证其具体的数据结构和行为
        // 3) 它是标准协议的实现，创建接口没有实际意义
        $callResponse = $this->createMock(JsonRpcCallResponse::class);

        $this->callResponseNormalizer->expects($this->once())
            ->method('normalize')
            ->with($callResponse)
            ->willReturn(null)
        ;

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
        $callRequest = new JsonRpcCallRequest();

        $this->callDenormalizer->expects($this->once())
            ->method('denormalize')
            ->with($data)
            ->willReturn($callRequest)
        ;

        $result = $this->serializer->denormalize($data);

        $this->assertSame($callRequest, $result);
    }

    public function testNormalizeWithValidResponseReturnsNormalizedData(): void
    {
        // 使用JsonRpcCallResponse具体类模拟的原因：
        // 1) 它是数据传输对象(DTO)，不需要抽象接口
        // 2) 测试需要验证其具体的数据结构和行为
        // 3) 它是标准协议的实现，创建接口没有实际意义
        $callResponse = $this->createMock(JsonRpcCallResponse::class);
        $normalizedData = ['jsonrpc' => '2.0', 'result' => 'test', 'id' => 1];

        $this->callResponseNormalizer->expects($this->once())
            ->method('normalize')
            ->with($callResponse)
            ->willReturn($normalizedData)
        ;

        $result = $this->serializer->normalize($callResponse);

        $this->assertSame($normalizedData, $result);
    }
}
