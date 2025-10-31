<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Event;

use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * 异常事件.
 *
 * 在 SDK 执行期间发生异常时分发（方法执行异常请参见 OnMethodFailureEvent）
 */
class OnExceptionEvent implements JsonRpcServerEvent
{
    private \Throwable $exception;

    public function getException(): \Throwable
    {
        return $this->exception;
    }

    public function setException(\Throwable $exception): void
    {
        $this->exception = $exception;
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
