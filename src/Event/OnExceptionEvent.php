<?php

namespace Tourze\JsonRPC\Core\Event;

use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * Class OnExceptionEvent
 *
 * Dispatched when an exception occurred during sdk execution (For method execution exception see OnMethodFailureEvent)
 */
class OnExceptionEvent implements JsonRpcServerEvent
{
    private \Throwable $exception;

    public function getException(): \Throwable
    {
        return $this->exception;
    }

    public function setException(\Throwable $exception): self
    {
        $this->exception = $exception;

        return $this;
    }

    private ?JsonRpcRequest $fromJsonRpcRequest = null;

    public function getFromJsonRpcRequest(): ?JsonRpcRequest
    {
        return $this->fromJsonRpcRequest;
    }

    public function setFromJsonRpcRequest(?JsonRpcRequest $fromJsonRpcRequest): void
    {
        $this->fromJsonRpcRequest = $fromJsonRpcRequest;
    }
}
