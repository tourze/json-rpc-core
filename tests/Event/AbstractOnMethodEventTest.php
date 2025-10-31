<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Event\AbstractOnMethodEvent;
use Tourze\JsonRPC\Core\Event\JsonRpcServerEvent;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * 测试AbstractOnMethodEvent抽象事件类.
 *
 * @internal
 */
#[CoversClass(AbstractOnMethodEvent::class)]
final class AbstractOnMethodEventTest extends TestCase
{
    private function createConcreteEvent(): AbstractOnMethodEvent
    {
        return new class extends AbstractOnMethodEvent {
            // 具体实现用于测试
        };
    }

    private function createMockMethod(): JsonRpcMethodInterface
    {
        return new class implements JsonRpcMethodInterface {
            public function __invoke(JsonRpcRequest $request): mixed
            {
                return ['success' => true];
            }

            public function execute(): array
            {
                return ['success' => true];
            }
        };
    }

    public function testSetAndGetJsonRpcRequest(): void
    {
        $event = $this->createConcreteEvent();
        $request = new JsonRpcRequest();
        $request->setMethod('test.method');
        $request->setId('123');
        $request->setParams(new JsonRpcParams(['test' => 'value']));

        $event->setJsonRpcRequest($request);

        $this->assertSame($request, $event->getJsonRpcRequest());
        $this->assertEquals('test.method', $event->getJsonRpcRequest()->getMethod());
        $this->assertEquals('123', $event->getJsonRpcRequest()->getId());
    }

    public function testSetAndGetMethod(): void
    {
        $event = $this->createConcreteEvent();
        $method = $this->createMockMethod();

        $event->setMethod($method);

        $this->assertSame($method, $event->getMethod());
    }

    public function testEventImplementsJsonRpcServerEvent(): void
    {
        $event = $this->createConcreteEvent();

        $this->assertInstanceOf(JsonRpcServerEvent::class, $event);
    }

    public function testEventWithCompleteData(): void
    {
        $event = $this->createConcreteEvent();

        $request = new JsonRpcRequest();
        $request->setMethod('user.create');
        $request->setId('456');
        $request->setParams(new JsonRpcParams(['name' => 'John', 'email' => 'john@example.com']));

        $method = $this->createMockMethod();

        $event->setJsonRpcRequest($request);
        $event->setMethod($method);

        // 验证所有数据都正确设置
        $this->assertEquals('user.create', $event->getJsonRpcRequest()->getMethod());
        $this->assertEquals('456', $event->getJsonRpcRequest()->getId());

        $params = $event->getJsonRpcRequest()->getParams();
        $this->assertNotNull($params);
        $this->assertEquals(['name' => 'John', 'email' => 'john@example.com'], $params->all());
        $this->assertSame($method, $event->getMethod());
    }
}
