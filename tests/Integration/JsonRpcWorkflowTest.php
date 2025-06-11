<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Exception\JsonRpcMethodNotFoundException;
use Tourze\JsonRPC\Core\Model\JsonRpcCallRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcCallResponse;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;

/**
 * 集成测试，测试完整的JSON-RPC工作流程
 */
class JsonRpcWorkflowTest extends TestCase
{
    /**
     * 模拟的JSON-RPC方法实现
     */
    private function createEchoMethod(): JsonRpcMethodInterface
    {
        return new class implements JsonRpcMethodInterface {
            public function __invoke(JsonRpcRequest $request): mixed
            {
                // 简单的回声方法，返回传入的参数
                return $request->getParams()->all();
            }

            public function execute(): array
            {
                return [];
            }
        };
    }

    private function createCalculatorMethod(): JsonRpcMethodInterface
    {
        return new class implements JsonRpcMethodInterface {
            public function __invoke(JsonRpcRequest $request): mixed
            {
                $params = $request->getParams();
                $a = $params->get('a');
                $b = $params->get('b');
                $op = $params->get('op');

                switch ($op) {
                    case 'add':
                        return $a + $b;
                    case 'subtract':
                        return $a - $b;
                    case 'multiply':
                        return $a * $b;
                    case 'divide':
                        if ($b == 0) {
                            throw new JsonRpcException(1, '除数不能为零');
                        }
                        return $a / $b;
                    default:
                        throw new JsonRpcException(2, '不支持的操作');
                }
            }

            public function execute(): array
            {
                return [];
            }
        };
    }

    /**
     * 简单的方法解析器
     */
    private function createMethodResolver(): callable
    {
        $echoMethod = $this->createEchoMethod();
        $calculatorMethod = $this->createCalculatorMethod();

        return function (string $methodName) use ($echoMethod, $calculatorMethod): JsonRpcMethodInterface {
            if ($methodName === 'echo') {
                return $echoMethod;
            } else if ($methodName === 'calculator') {
                return $calculatorMethod;
            } else {
                throw new JsonRpcMethodNotFoundException($methodName, ['available_methods' => ['echo', 'calculator']]);
            }
        };
    }

    /**
     * 测试单个JSON-RPC请求的处理
     */
    public function testSingleRequest(): void
    {
        $methodResolver = $this->createMethodResolver();

        // 创建请求
        $request = new JsonRpcRequest();
        $request->setJsonrpc('2.0');
        $request->setMethod('echo');
        $request->setId('1');
        $request->setParams(new JsonRpcParams(['message' => 'Hello, World!']));

        // 创建调用请求
        $callRequest = new JsonRpcCallRequest(false);
        $callRequest->addRequestItem($request);

        // 创建响应
        $callResponse = new JsonRpcCallResponse(false);

        // 处理每个请求项
        foreach ($callRequest->getItemList() as $item) {
            if ($item instanceof JsonRpcRequest) {
                $response = new JsonRpcResponse();
                $response->setJsonrpc('2.0');
                $response->setId($item->getId());
                $response->setIsNotification($item->isNotification());

                try {
                    $methodName = $item->getMethod();
                    $method = $methodResolver($methodName);
                    $result = $method($item);
                    $response->setResult($result);
                } catch (JsonRpcException $e) {
                    $response->setError($e);
                } catch (\Throwable $e) {
                    // 将普通异常包装为JSON-RPC异常
                    $jsonRpcException = new JsonRpcException(
                        -32000,
                        $e->getMessage(),
                        ['exception' => get_class($e)]
                    );
                    $response->setError($jsonRpcException);
                }

                $callResponse->addResponse($response);
            } else if ($item instanceof \Exception) {
                $response = new JsonRpcResponse();
                $response->setJsonrpc('2.0');
                $response->setId(null);

                // 将异常包装为JSON-RPC异常
                $jsonRpcException = new JsonRpcException(
                    -32000,
                    $item->getMessage(),
                    ['exception' => get_class($item)]
                );
                $response->setError($jsonRpcException);

                $callResponse->addResponse($response);
            }
        }

        // 检查响应
        $responses = $callResponse->getResponseList();
        $this->assertCount(1, $responses);

        $response = $responses[0];
        $this->assertEquals('2.0', $response->getJsonrpc());
        $this->assertEquals('1', $response->getId());
        $this->assertEquals(['message' => 'Hello, World!'], $response->getResult());
        $this->assertNull($response->getError());
    }

    /**
     * 测试批量JSON-RPC请求的处理
     */
    public function testBatchRequest(): void
    {
        $methodResolver = $this->createMethodResolver();

        // 创建批量请求
        $callRequest = new JsonRpcCallRequest(true);

        // 请求1：回声
        $request1 = new JsonRpcRequest();
        $request1->setJsonrpc('2.0');
        $request1->setMethod('echo');
        $request1->setId('1');
        $request1->setParams(new JsonRpcParams(['message' => 'Request 1']));
        $callRequest->addRequestItem($request1);

        // 请求2：计算器 - 加法
        $request2 = new JsonRpcRequest();
        $request2->setJsonrpc('2.0');
        $request2->setMethod('calculator');
        $request2->setId('2');
        $request2->setParams(new JsonRpcParams(['a' => 10, 'b' => 5, 'op' => 'add']));
        $callRequest->addRequestItem($request2);

        // 请求3：计算器 - 除以零错误
        $request3 = new JsonRpcRequest();
        $request3->setJsonrpc('2.0');
        $request3->setMethod('calculator');
        $request3->setId('3');
        $request3->setParams(new JsonRpcParams(['a' => 10, 'b' => 0, 'op' => 'divide']));
        $callRequest->addRequestItem($request3);

        // 请求4：方法不存在
        $request4 = new JsonRpcRequest();
        $request4->setJsonrpc('2.0');
        $request4->setMethod('unknown_method');
        $request4->setId('4');
        $request4->setParams(new JsonRpcParams());
        $callRequest->addRequestItem($request4);

        // 创建响应
        $callResponse = new JsonRpcCallResponse(true);

        // 处理每个请求项
        foreach ($callRequest->getItemList() as $item) {
            if ($item instanceof JsonRpcRequest) {
                $response = new JsonRpcResponse();
                $response->setJsonrpc('2.0');
                $response->setId($item->getId());
                $response->setIsNotification($item->isNotification());

                try {
                    $methodName = $item->getMethod();
                    $method = $methodResolver($methodName);
                    $result = $method($item);
                    $response->setResult($result);
                } catch (JsonRpcException $e) {
                    $response->setError($e);
                } catch (\Throwable $e) {
                    // 将普通异常包装为JSON-RPC异常
                    $jsonRpcException = new JsonRpcException(
                        -32000,
                        $e->getMessage(),
                        ['exception' => get_class($e)]
                    );
                    $response->setError($jsonRpcException);
                }

                $callResponse->addResponse($response);
            } else if ($item instanceof \Exception) {
                $response = new JsonRpcResponse();
                $response->setJsonrpc('2.0');
                $response->setId(null);

                // 将异常包装为JSON-RPC异常
                $jsonRpcException = new JsonRpcException(
                    -32000,
                    $item->getMessage(),
                    ['exception' => get_class($item)]
                );
                $response->setError($jsonRpcException);

                $callResponse->addResponse($response);
            }
        }

        // 检查响应
        $responses = $callResponse->getResponseList();
        $this->assertCount(4, $responses);

        // 检查响应1：回声
        $response1 = $responses[0];
        $this->assertEquals('1', $response1->getId());
        $this->assertEquals(['message' => 'Request 1'], $response1->getResult());
        $this->assertNull($response1->getError());

        // 检查响应2：计算器加法
        $response2 = $responses[1];
        $this->assertEquals('2', $response2->getId());
        $this->assertEquals(15, $response2->getResult());
        $this->assertNull($response2->getError());

        // 检查响应3：除以零错误
        $response3 = $responses[2];
        $this->assertEquals('3', $response3->getId());
        $this->assertNull($response3->getResult());
        $this->assertInstanceOf(JsonRpcException::class, $response3->getError());
        $this->assertEquals(1, $response3->getError()->getErrorCode());
        $this->assertEquals('除数不能为零', $response3->getError()->getErrorMessage());

        // 检查响应4：方法不存在
        $response4 = $responses[3];
        $this->assertEquals('4', $response4->getId());
        $this->assertNull($response4->getResult());
        $this->assertInstanceOf(JsonRpcException::class, $response4->getError());
        $this->assertEquals(-32601, $response4->getError()->getErrorCode());
        $this->assertEquals('Method not found', $response4->getError()->getErrorMessage());
    }

    /**
     * 测试通知请求（无ID）
     */
    public function testNotification(): void
    {
        $methodResolver = $this->createMethodResolver();

        // 创建通知请求
        $request = new JsonRpcRequest();
        $request->setJsonrpc('2.0');
        $request->setMethod('echo');
        // 不设置ID，使其成为通知
        $request->setParams(new JsonRpcParams(['message' => 'This is a notification']));

        // 验证它是一个通知
        $this->assertTrue($request->isNotification());

        // 创建调用请求
        $callRequest = new JsonRpcCallRequest(false);
        $callRequest->addRequestItem($request);

        // 创建响应
        $callResponse = new JsonRpcCallResponse(false);

        // 处理请求
        foreach ($callRequest->getItemList() as $item) {
            if ($item instanceof JsonRpcRequest) {
                // 如果是通知，不需要响应
                if ($item->isNotification()) {
                    try {
                        $methodName = $item->getMethod();
                        $method = $methodResolver($methodName);
                        $method($item); // 执行但忽略结果
                    } catch (\Throwable $e) {
                        // 通知不需要返回错误
                    }
                } else {
                    $response = new JsonRpcResponse();
                    $response->setJsonrpc('2.0');
                    $response->setId($item->getId());

                    try {
                        $methodName = $item->getMethod();
                        $method = $methodResolver($methodName);
                        $result = $method($item);
                        $response->setResult($result);
                    } catch (JsonRpcException $e) {
                        $response->setError($e);
                    } catch (\Throwable $e) {
                        $jsonRpcException = new JsonRpcException(-32000, $e->getMessage());
                        $response->setError($jsonRpcException);
                    }

                    $callResponse->addResponse($response);
                }
            }
        }

        // 通知不应有响应
        $this->assertEmpty($callResponse->getResponseList());
    }
}
