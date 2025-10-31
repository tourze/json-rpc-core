<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Contracts;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Tourze\JsonRPC\Core\Contracts\EndpointInterface;

/**
 * 测试EndpointInterface接口.
 *
 * @internal
 */
#[CoversClass(EndpointInterface::class)]
final class EndpointInterfaceTest extends TestCase
{
    private function createMockEndpoint(): EndpointInterface
    {
        return new class($this) implements EndpointInterface {
            public function __construct(private readonly TestCase $testCase)
            {
            }

            public function index(string $payload, ?Request $request = null): string
            {
                $decoded = json_decode($payload, true);
                $this->testCase->assertIsArray($decoded);

                if (isset($decoded['method']) && 'echo' === $decoded['method']) {
                    $result = json_encode([
                        'jsonrpc' => '2.0',
                        'result' => $decoded['params'] ?? [],
                        'id' => $decoded['id'] ?? null,
                    ]);

                    return false !== $result ? $result : '{"jsonrpc":"2.0","error":{"code":-32603,"message":"Internal error"},"id":null}';
                }

                $result = json_encode([
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32601,
                        'message' => 'Method not found',
                    ],
                    'id' => $decoded['id'] ?? null,
                ]);

                return false !== $result ? $result : '{"jsonrpc":"2.0","error":{"code":-32603,"message":"Internal error"},"id":null}';
            }
        };
    }

    public function testInterfaceImplementation(): void
    {
        $endpoint = $this->createMockEndpoint();

        $this->assertInstanceOf(EndpointInterface::class, $endpoint);
    }

    public function testIndexMethodWithValidPayload(): void
    {
        $endpoint = $this->createMockEndpoint();
        $payloadJson = json_encode([
            'jsonrpc' => '2.0',
            'method' => 'echo',
            'params' => ['message' => 'Hello, World!'],
            'id' => '1',
        ]);
        $payload = false !== $payloadJson ? $payloadJson : '{}';

        $result = $endpoint->index($payload);

        $decoded = json_decode($result, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('2.0', $decoded['jsonrpc']);
        $this->assertEquals(['message' => 'Hello, World!'], $decoded['result']);
        $this->assertEquals('1', $decoded['id']);
    }

    public function testIndexMethodWithRequest(): void
    {
        $endpoint = $this->createMockEndpoint();
        $payloadJson = json_encode([
            'jsonrpc' => '2.0',
            'method' => 'echo',
            'params' => ['data' => 'test'],
            'id' => '2',
        ]);
        $payload = false !== $payloadJson ? $payloadJson : '{}';
        $request = Request::create('/json-rpc', 'POST', [], [], [], [], $payload);

        $result = $endpoint->index($payload, $request);

        $decoded = json_decode($result, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('2.0', $decoded['jsonrpc']);
        $this->assertEquals(['data' => 'test'], $decoded['result']);
        $this->assertEquals('2', $decoded['id']);
    }

    public function testIndexMethodWithNullRequest(): void
    {
        $endpoint = $this->createMockEndpoint();
        $payloadJson = json_encode([
            'jsonrpc' => '2.0',
            'method' => 'echo',
            'params' => [],
            'id' => '3',
        ]);
        $payload = false !== $payloadJson ? $payloadJson : '{}';

        $result = $endpoint->index($payload, null);

        $decoded = json_decode($result, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('2.0', $decoded['jsonrpc']);
        $this->assertEquals([], $decoded['result']);
        $this->assertEquals('3', $decoded['id']);
    }

    public function testIndexMethodWithUnknownMethod(): void
    {
        $endpoint = $this->createMockEndpoint();
        $payloadJson = json_encode([
            'jsonrpc' => '2.0',
            'method' => 'unknown_method',
            'params' => [],
            'id' => '4',
        ]);
        $payload = false !== $payloadJson ? $payloadJson : '{}';

        $result = $endpoint->index($payload);

        $decoded = json_decode($result, true);
        $this->assertIsArray($decoded);
        $this->assertEquals('2.0', $decoded['jsonrpc']);
        $this->assertArrayHasKey('error', $decoded);
        $this->assertIsArray($decoded['error']);
        $this->assertEquals(-32601, $decoded['error']['code']);
        $this->assertEquals('Method not found', $decoded['error']['message']);
        $this->assertEquals('4', $decoded['id']);
    }

    public function testIndexMethodReturnType(): void
    {
        $endpoint = $this->createMockEndpoint();
        $payload = '{"jsonrpc":"2.0","method":"echo","id":"test"}';

        $result = $endpoint->index($payload);
        $this->assertJson($result);
    }

    public function testIndexMethodWithEmptyPayload(): void
    {
        $endpoint = $this->createMockEndpoint();
        $payload = '{}';

        $result = $endpoint->index($payload);
        // 具体的错误处理取决于实现
        $decoded = json_decode($result, true);

        // 验证返回了有效的 JSON
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('jsonrpc', $decoded);
    }
}
