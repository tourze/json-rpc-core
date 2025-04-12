<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Domain;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

class JsonRpcMethodTest extends TestCase
{
    /**
     * 创建一个模拟的JSON-RPC方法实现
     */
    private function createSuccessfulMethod(): JsonRpcMethodInterface
    {
        return new class implements JsonRpcMethodInterface {
            private array $result = ['success' => true, 'data' => 'test data'];

            public function __invoke(JsonRpcRequest $request): mixed
            {
                return $this->result;
            }

            public function execute(): array
            {
                return $this->result;
            }
        };
    }

    /**
     * 创建一个始终抛出异常的JSON-RPC方法实现
     */
    private function createFailingMethod(): JsonRpcMethodInterface
    {
        return new class implements JsonRpcMethodInterface {
            public function __invoke(JsonRpcRequest $request): mixed
            {
                throw new JsonRpcException(100, 'Method execution failed');
            }

            public function execute(): array
            {
                throw new JsonRpcException(100, 'Method execution failed');
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

        $this->assertEquals(['success' => true, 'data' => 'test data'], $result);
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
        $result = $method->execute();

        $this->assertEquals(['success' => true, 'data' => 'test data'], $result);
    }

    public function testExecuteMethodFailure(): void
    {
        $method = $this->createFailingMethod();

        $this->expectException(JsonRpcException::class);
        $this->expectExceptionMessage('Method execution failed');
        $this->expectExceptionCode(100);

        $method->execute();
    }
}
