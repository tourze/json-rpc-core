<?php

namespace Tourze\JsonRPC\Core\Event;

use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * Class AbstractOnMethodEvent
 */
abstract class AbstractOnMethodEvent implements JsonRpcServerEvent
{
    private JsonRpcRequest $jsonRpcRequest;

    public function getJsonRpcRequest(): JsonRpcRequest
    {
        return $this->jsonRpcRequest;
    }

    public function setJsonRpcRequest(JsonRpcRequest $jsonRpcRequest): void
    {
        $this->jsonRpcRequest = $jsonRpcRequest;
    }

    private JsonRpcMethodInterface $method;

    public function getMethod(): JsonRpcMethodInterface
    {
        return $this->method;
    }

    public function setMethod(JsonRpcMethodInterface $method): void
    {
        $this->method = $method;
    }
}
