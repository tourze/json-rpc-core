<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Model;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Model\JsonRpcCallRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * @internal
 */
#[CoversClass(JsonRpcCallRequest::class)]
final class JsonRpcCallRequestTest extends TestCase
{
    public function testNonBatchRequest(): void
    {
        $callRequest = new JsonRpcCallRequest(false);

        $this->assertFalse($callRequest->isBatch());
        $this->assertEmpty($callRequest->getItemList());
    }

    public function testBatchRequest(): void
    {
        $callRequest = new JsonRpcCallRequest(true);

        $this->assertTrue($callRequest->isBatch());
    }

    public function testAddRequestItem(): void
    {
        $callRequest = new JsonRpcCallRequest();

        $request1 = new JsonRpcRequest();
        $request1->setMethod('method1');
        $request1->setId(1);
        $request1->setParams(new JsonRpcParams(['foo' => 'bar']));

        $request2 = new JsonRpcRequest();
        $request2->setMethod('method2');
        $request2->setId(2);
        $request2->setParams(new JsonRpcParams(['baz' => 'qux']));

        $callRequest->addRequestItem($request1);
        $callRequest->addRequestItem($request2);

        $items = $callRequest->getItemList();

        $this->assertCount(2, $items);
        $this->assertSame($request1, $items[0]);
        $this->assertSame($request2, $items[1]);
    }

    public function testAddExceptionItem(): void
    {
        $callRequest = new JsonRpcCallRequest(true);

        $exception1 = new \Exception('Error 1');
        $exception2 = new \RuntimeException('Error 2');

        $callRequest->addExceptionItem($exception1);
        $callRequest->addExceptionItem($exception2);

        $items = $callRequest->getItemList();

        $this->assertCount(2, $items);
        $this->assertSame($exception1, $items[0]);
        $this->assertSame($exception2, $items[1]);
    }

    public function testMixedItems(): void
    {
        $callRequest = new JsonRpcCallRequest(true);

        $request = new JsonRpcRequest();
        $request->setMethod('test');
        $request->setId(123);
        $request->setParams(new JsonRpcParams());

        $exception = new \Exception('Test error');

        $callRequest->addRequestItem($request);
        $callRequest->addExceptionItem($exception);

        $items = $callRequest->getItemList();

        $this->assertCount(2, $items);
        $this->assertSame($request, $items[0]);
        $this->assertSame($exception, $items[1]);
    }
}
