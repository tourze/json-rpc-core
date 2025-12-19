<?php

namespace Tourze\JsonRPC\Core\Tests\Serialization;

use PHPUnit\Framework\Attributes\CoversClass;
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

    private JsonRpcResponseNormalizer $responseNormalizer;

    protected function setUp(): void
    {
        // 使用真实的 JsonRpcResponseNormalizer 实例
        $this->responseNormalizer = new JsonRpcResponseNormalizer();

        // 创建 JsonRpcCallResponseNormalizer 实例
        $this->normalizer = new JsonRpcCallResponseNormalizer($this->responseNormalizer);
    }

    public function testNormalizeWithSingleResponseReturnsNormalizedResponse(): void
    {
        // 使用真实的 JsonRpcResponse 实例
        $response = new JsonRpcResponse();
        $response->setId(1);
        $response->setResult('test');

        $callResponse = new JsonRpcCallResponse(false);
        $callResponse->addResponse($response);

        $result = $this->normalizer->normalize($callResponse);

        $this->assertIsArray($result);
        $this->assertSame('2.0', $result['jsonrpc']);
        $this->assertSame('test', $result['result']);
        $this->assertSame(1, $result['id']);
    }

    public function testNormalizeWithBatchResponseReturnsArrayOfNormalizedResponses(): void
    {
        // 使用真实的 JsonRpcResponse 实例
        $response1 = new JsonRpcResponse();
        $response1->setId(1);
        $response1->setResult('test1');

        $response2 = new JsonRpcResponse();
        $response2->setId(2);
        $response2->setResult('test2');

        $callResponse = new JsonRpcCallResponse(true);
        $callResponse->addResponse($response1);
        $callResponse->addResponse($response2);

        $result = $this->normalizer->normalize($callResponse);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);

        $this->assertSame('2.0', $result[0]['jsonrpc']);
        $this->assertSame('test1', $result[0]['result']);
        $this->assertSame(1, $result[0]['id']);

        $this->assertSame('2.0', $result[1]['jsonrpc']);
        $this->assertSame('test2', $result[1]['result']);
        $this->assertSame(2, $result[1]['id']);
    }

    public function testNormalizeWithNotificationOnlyResponseReturnsNull(): void
    {
        // 使用真实的 JsonRpcResponse 实例，设置为通知
        $response = new JsonRpcResponse();
        $response->setIsNotification(true);
        $response->setResult('test');

        $callResponse = new JsonRpcCallResponse(false);
        $callResponse->addResponse($response);

        $result = $this->normalizer->normalize($callResponse);

        $this->assertNull($result);
    }

    public function testNormalizeWithMixedNotificationAndNormalResponseReturnsOnlyNormalResponse(): void
    {
        // 使用真实的 JsonRpcResponse 实例
        $notificationResponse = new JsonRpcResponse();
        $notificationResponse->setIsNotification(true);
        $notificationResponse->setResult('notification');

        $normalResponse = new JsonRpcResponse();
        $normalResponse->setId(1);
        $normalResponse->setResult('test');

        $callResponse = new JsonRpcCallResponse(true);
        $callResponse->addResponse($notificationResponse);
        $callResponse->addResponse($normalResponse);

        $result = $this->normalizer->normalize($callResponse);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertSame('2.0', $result[0]['jsonrpc']);
        $this->assertSame('test', $result[0]['result']);
        $this->assertSame(1, $result[0]['id']);
    }

    public function testNormalizeWithBatchNotificationOnlyResponsesReturnsNull(): void
    {
        // 使用真实的 JsonRpcResponse 实例，都设置为通知
        $response1 = new JsonRpcResponse();
        $response1->setIsNotification(true);
        $response1->setResult('test1');

        $response2 = new JsonRpcResponse();
        $response2->setIsNotification(true);
        $response2->setResult('test2');

        $callResponse = new JsonRpcCallResponse(true);
        $callResponse->addResponse($response1);
        $callResponse->addResponse($response2);

        $result = $this->normalizer->normalize($callResponse);

        $this->assertNull($result);
    }

    public function testNormalizeWithEmptyResponseReturnsNull(): void
    {
        $callResponse = new JsonRpcCallResponse(false);

        $result = $this->normalizer->normalize($callResponse);

        $this->assertNull($result);
    }
}
