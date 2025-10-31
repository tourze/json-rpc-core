<?php

namespace Tourze\JsonRPC\Core\Tests\Serialization;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Model\JsonRpcCallResponse;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;
use Tourze\JsonRPC\Core\Serialization\JsonRpcCallResponseNormalizer;
use Tourze\JsonRPC\Core\Serialization\JsonRpcResponseNormalizer;

/**
 * @internal
 */
#[CoversClass(JsonRpcCallResponseNormalizer::class)]
final class JsonRpcCallResponseNormalizerTest extends TestCase
{
    private JsonRpcCallResponseNormalizer $normalizer;

    private JsonRpcResponseNormalizer&MockObject $responseNormalizer;

    protected function setUp(): void
    {
        // 创建mock对象
        $this->responseNormalizer = $this->createMock(JsonRpcResponseNormalizer::class);

        // 直接创建JsonRpcCallResponseNormalizer实例（使用mock依赖）
        $this->normalizer = new JsonRpcCallResponseNormalizer($this->responseNormalizer);
    }

    public function testNormalizeWithSingleResponseReturnsNormalizedResponse(): void
    {
        // 使用JsonRpcResponse具体类模拟的原因：
        // 1) 它是数据传输对象(DTO)，不需要抽象接口
        // 2) 测试需要验证其具体的数据结构和行为
        // 3) 它是标准协议的实现，创建接口没有实际意义
        $response = $this->createMock(JsonRpcResponse::class);
        $response->expects($this->once())
            ->method('isNotification')
            ->willReturn(false)
        ;

        $callResponse = new JsonRpcCallResponse(false);
        $callResponse->addResponse($response);

        $normalizedData = ['jsonrpc' => '2.0', 'result' => 'test', 'id' => 1];

        $this->responseNormalizer->expects($this->once())
            ->method('normalize')
            ->with($response)
            ->willReturn($normalizedData)
        ;

        $result = $this->normalizer->normalize($callResponse);

        $this->assertSame($normalizedData, $result);
    }

    public function testNormalizeWithBatchResponseReturnsArrayOfNormalizedResponses(): void
    {
        // 使用JsonRpcResponse具体类模拟的原因：
        // 1) 它是数据传输对象(DTO)，不需要抽象接口
        // 2) 测试需要验证其具体的数据结构和行为
        // 3) 它是标准协议的实现，创建接口没有实际意义
        $response1 = $this->createMock(JsonRpcResponse::class);
        $response1->expects($this->once())
            ->method('isNotification')
            ->willReturn(false)
        ;

        // 使用JsonRpcResponse具体类模拟的原因：
        // 1) 它是数据传输对象(DTO)，不需要抽象接口
        // 2) 测试需要验证其具体的数据结构和行为
        // 3) 它是标准协议的实现，创建接口没有实际意义
        $response2 = $this->createMock(JsonRpcResponse::class);
        $response2->expects($this->once())
            ->method('isNotification')
            ->willReturn(false)
        ;

        $callResponse = new JsonRpcCallResponse(true);
        $callResponse->addResponse($response1);
        $callResponse->addResponse($response2);

        $normalizedData1 = ['jsonrpc' => '2.0', 'result' => 'test1', 'id' => 1];
        $normalizedData2 = ['jsonrpc' => '2.0', 'result' => 'test2', 'id' => 2];

        $this->responseNormalizer->expects($this->exactly(2))
            ->method('normalize')
            ->willReturnOnConsecutiveCalls($normalizedData1, $normalizedData2)
        ;

        $result = $this->normalizer->normalize($callResponse);
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame($normalizedData1, $result[0]);
        $this->assertSame($normalizedData2, $result[1]);
    }

    public function testNormalizeWithNotificationOnlyResponseReturnsNull(): void
    {
        // 使用JsonRpcResponse具体类模拟的原因：
        // 1) 它是数据传输对象(DTO)，不需要抽象接口
        // 2) 测试需要验证其具体的数据结构和行为
        // 3) 它是标准协议的实现，创建接口没有实际意义
        $response = $this->createMock(JsonRpcResponse::class);
        $response->expects($this->once())
            ->method('isNotification')
            ->willReturn(true)
        ;

        $callResponse = new JsonRpcCallResponse(false);
        $callResponse->addResponse($response);

        $this->responseNormalizer->expects($this->never())
            ->method('normalize')
        ;

        $result = $this->normalizer->normalize($callResponse);

        $this->assertNull($result);
    }

    public function testNormalizeWithMixedNotificationAndNormalResponseReturnsOnlyNormalResponse(): void
    {
        // 使用JsonRpcResponse具体类模拟的原因：
        // 1) 它是数据传输对象(DTO)，不需要抽象接口
        // 2) 测试需要验证其具体的数据结构和行为
        // 3) 它是标准协议的实现，创建接口没有实际意义
        $notificationResponse = $this->createMock(JsonRpcResponse::class);
        $notificationResponse->expects($this->once())
            ->method('isNotification')
            ->willReturn(true)
        ;

        // 使用JsonRpcResponse具体类模拟的原因：
        // 1) 它是数据传输对象(DTO)，不需要抽象接口
        // 2) 测试需要验证其具体的数据结构和行为
        // 3) 它是标准协议的实现，创建接口没有实际意义
        $normalResponse = $this->createMock(JsonRpcResponse::class);
        $normalResponse->expects($this->once())
            ->method('isNotification')
            ->willReturn(false)
        ;

        $callResponse = new JsonRpcCallResponse(true);
        $callResponse->addResponse($notificationResponse);
        $callResponse->addResponse($normalResponse);

        $normalizedData = ['jsonrpc' => '2.0', 'result' => 'test', 'id' => 1];

        $this->responseNormalizer->expects($this->once())
            ->method('normalize')
            ->with($normalResponse)
            ->willReturn($normalizedData)
        ;

        $result = $this->normalizer->normalize($callResponse);
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame($normalizedData, $result[0]);
    }

    public function testNormalizeWithBatchNotificationOnlyResponsesReturnsNull(): void
    {
        // 使用JsonRpcResponse具体类模拟的原因：
        // 1) 它是数据传输对象(DTO)，不需要抽象接口
        // 2) 测试需要验证其具体的数据结构和行为
        // 3) 它是标准协议的实现，创建接口没有实际意义
        $response1 = $this->createMock(JsonRpcResponse::class);
        $response1->expects($this->once())
            ->method('isNotification')
            ->willReturn(true)
        ;

        // 使用JsonRpcResponse具体类模拟的原因：
        // 1) 它是数据传输对象(DTO)，不需要抽象接口
        // 2) 测试需要验证其具体的数据结构和行为
        // 3) 它是标准协议的实现，创建接口没有实际意义
        $response2 = $this->createMock(JsonRpcResponse::class);
        $response2->expects($this->once())
            ->method('isNotification')
            ->willReturn(true)
        ;

        $callResponse = new JsonRpcCallResponse(true);
        $callResponse->addResponse($response1);
        $callResponse->addResponse($response2);

        $this->responseNormalizer->expects($this->never())
            ->method('normalize')
        ;

        $result = $this->normalizer->normalize($callResponse);

        $this->assertNull($result);
    }

    public function testNormalizeWithEmptyResponseReturnsNull(): void
    {
        $callResponse = new JsonRpcCallResponse(false);

        $this->responseNormalizer->expects($this->never())
            ->method('normalize')
        ;

        $result = $this->normalizer->normalize($callResponse);

        $this->assertNull($result);
    }
}
