<?php

namespace Tourze\JsonRPC\Core\Tests\Serialization;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Exception\JsonRpcInternalErrorException;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;
use Tourze\JsonRPC\Core\Serialization\JsonRpcResponseNormalizer;
use Tourze\JsonRPC\Core\Tests\Exception\TestJsonRpcException;

/**
 * @internal
 */
#[CoversClass(JsonRpcResponseNormalizer::class)]
final class JsonRpcResponseNormalizerTest extends TestCase
{
    private JsonRpcResponseNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new JsonRpcResponseNormalizer();
    }

    public function testNormalizeWithSuccessResponseReturnsNormalizedData(): void
    {
        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setId(1);
        $response->setResult(['status' => 'success']);

        $result = $this->normalizer->normalize($response);

        $expectedData = [
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => ['status' => 'success'],
        ];

        $this->assertSame($expectedData, $result);
    }

    public function testNormalizeWithErrorResponseReturnsNormalizedErrorData(): void
    {
        $exception = new JsonRpcInternalErrorException(new \Exception('Test error'));
        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setId(1);
        $response->setError($exception);

        $result = $this->normalizer->normalize($response);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('jsonrpc', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayNotHasKey('result', $result);

        $this->assertSame('2.0', $result['jsonrpc']);
        $this->assertSame(1, $result['id']);
        $this->assertIsArray($result['error']);
        $this->assertArrayHasKey('code', $result['error']);
        $this->assertArrayHasKey('message', $result['error']);
    }

    public function testNormalizeWithNotificationReturnsNull(): void
    {
        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setIsNotification(true);
        $response->setResult(['status' => 'success']);

        $result = $this->normalizer->normalize($response);

        $this->assertNull($result);
    }

    public function testNormalizeWithNullResultIncludesNullResult(): void
    {
        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setId(1);
        $response->setResult(null);

        $result = $this->normalizer->normalize($response);

        $expectedData = [
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => null,
        ];

        $this->assertSame($expectedData, $result);
    }

    public function testNormalizeWithStringIdPreservesStringId(): void
    {
        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setId('string-id');
        $response->setResult(['data' => 'test']);

        $result = $this->normalizer->normalize($response);

        $this->assertIsArray($result);
        $this->assertSame('string-id', $result['id']);
    }

    public function testNormalizeWithZeroIdPreservesZeroId(): void
    {
        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setId(0);
        $response->setResult(['data' => 'test']);

        $result = $this->normalizer->normalize($response);

        $this->assertIsArray($result);
        $this->assertSame(0, $result['id']);
    }

    public function testNormalizeErrorWithErrorDataIncludedIncludesDataField(): void
    {
        $exception = new JsonRpcInternalErrorException(new \Exception('Test error'));

        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setId(1);
        $response->setError($exception);

        $result = $this->normalizer->normalize($response);

        // JsonRpcInternalErrorException 会自动包含前一个异常的信息
        $this->assertIsArray($result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsArray($result['error']);
        $this->assertArrayHasKey('data', $result['error']);
        $this->assertIsArray($result['error']['data']);
        $this->assertArrayHasKey('previous', $result['error']['data']);
        $this->assertIsString($result['error']['data']['previous']);
        $this->assertStringContainsString('Test error', $result['error']['data']['previous']);
    }

    public function testNormalizeWithComplexResultPreservesStructure(): void
    {
        $complexResult = [
            'users' => [
                ['id' => 1, 'name' => 'John'],
                ['id' => 2, 'name' => 'Jane'],
            ],
            'meta' => [
                'total' => 2,
                'page' => 1,
            ],
        ];

        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setId(1);
        $response->setResult($complexResult);

        $result = $this->normalizer->normalize($response);

        $this->assertIsArray($result);
        $this->assertSame($complexResult, $result['result']);
    }

    public function testNormalizeWithErrorNotificationReturnsNull(): void
    {
        $exception = new JsonRpcInternalErrorException(new \Exception('Test error'));
        $response = new JsonRpcResponse();
        $response->setJsonrpc('2.0');
        $response->setIsNotification(true);
        $response->setError($exception);

        $result = $this->normalizer->normalize($response);

        $this->assertNull($result);
    }

    public function testNormalizeErrorWithoutErrorDataDoesNotIncludeDataField(): void
    {
        // 创建一个没有 data 的 JsonRpcException
        $exception = new TestJsonRpcException(-32000, 'Test error');

        // 使用反射调用私有方法
        $method = new \ReflectionMethod(JsonRpcResponseNormalizer::class, 'normalizeError');
        $method->setAccessible(true);
        $method->invoke($this->normalizer, $exception);
        $result = $method->invoke($this->normalizer, $exception);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('message', $result);

        // 验证没有 data 字段
        $this->assertArrayNotHasKey('data', $result);
    }
}
