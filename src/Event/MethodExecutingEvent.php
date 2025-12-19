<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;

final class MethodExecutingEvent extends Event
{
    private JsonRpcRequest $item;

    public function __construct(JsonRpcRequest $item, ?JsonRpcResponse $response = null)
    {
        $this->item = $item;
        $this->response = $response;
    }

    public function getItem(): JsonRpcRequest
    {
        return $this->item;
    }

    public function setItem(JsonRpcRequest $item): void
    {
        $this->item = $item;
    }

    private ?JsonRpcResponse $response = null;

    public function getResponse(): ?JsonRpcResponse
    {
        return $this->response;
    }

    public function setResponse(?JsonRpcResponse $response): void
    {
        $this->response = $response;
    }
}
