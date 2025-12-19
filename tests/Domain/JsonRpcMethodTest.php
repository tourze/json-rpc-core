<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Domain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;
use Tourze\JsonRPC\Core\Result\SuccessResult;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Tests\Exception\TestJsonRpcException;
use Tourze\JsonRPC\Core\Tests\Fixtures\TestJsonRpcMethodParam;

/**
 * @internal
 */
#[CoversClass(JsonRpcMethodInterface::class)]
final class JsonRpcMethodTest extends TestCase
{
    /**
     * 创建一个模拟的 JSON-RPC 方法实现
     */
    private function createSuccessfulMethod(): JsonRpcMethodInterface
    {
        return new class implements JsonRpcMethodInterface {
            public function __invoke(JsonRpcRequest $request): RpcResultInterface
            {
                return new SuccessResult(success: true, message: 'test data');
            }

            public function execute(RpcParamInterface $param): RpcResultInterface
            {
                return new SuccessResult(success: true, message: 'test data');
            }
        };
    }

    /**
     * 创建一个始终抛出异常的 JSON-RPC 方法实现
     */
    private function createFailingMethod(): JsonRpcMethodInterface
    {
        return new class implements JsonRpcMethodInterface {
            public function __invoke(JsonRpcRequest $request): RpcResultInterface
            {
                throw new TestJsonRpcException(100, 'Method execution failed');
            }

            public function execute(RpcParamInterface $param): RpcResultInterface
            {
                throw new TestJsonRpcException(100, 'Method execution failed');
            }
        };
    }

    public function testMethodSuccess(): void
    {
        $method = $this->createSuccessfulMethod();
        $request = new JsonRpcRequest();
        $request->setMethod('test.method');
        $request->setId('1');
        $request->setParams(new JsonRpcParams(['param1' => 'value1']));

        $result = $method($request);

        $this->assertInstanceOf(RpcResultInterface::class, $result);
        $this->assertInstanceOf(SuccessResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertSame('test data', $result->message);
    }

    public function testMethodFailure(): void
    {
        $method = $this->createFailingMethod();
        $request = new JsonRpcRequest();
        $request->setMethod('test.method');
        $request->setId('1');
        $request->setParams(new JsonRpcParams(['param1' => 'value1']));

        $this->expectException(JsonRpcException::class);
        $this->expectExceptionMessage('Method execution failed');
        $this->expectExceptionCode(100);

        $method($request);
    }

    public function testExecuteMethod(): void
    {
        $method = $this->createSuccessfulMethod();
        $param = new TestJsonRpcMethodParam();
        $result = $method->execute($param);

        $this->assertInstanceOf(RpcResultInterface::class, $result);
        $this->assertInstanceOf(SuccessResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertSame('test data', $result->message);
    }

    public function testExecuteMethodFailure(): void
    {
        $method = $this->createFailingMethod();
        $param = new TestJsonRpcMethodParam();

        $this->expectException(JsonRpcException::class);
        $this->expectExceptionMessage('Method execution failed');
        $this->expectExceptionCode(100);

        $method->execute($param);
    }
}
