<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Event;

use Symfony\Component\HttpFoundation\Request;
use Tourze\JsonRPC\Core\Model\JsonRpcCallRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcCallResponse;

/**
 * 响应发送事件.
 *
 * 在响应已被端点成功序列化并即将返回时分发
 */
class ResponseSendingEvent implements JsonRpcServerEvent
{
    private string $responseString;

    private JsonRpcCallResponse $jsonRpcCallResponse;

    private ?JsonRpcCallRequest $jsonRpcCall = null;

    public function getJsonRpcCallResponse(): JsonRpcCallResponse
    {
        return $this->jsonRpcCallResponse;
    }

    public function getJsonRpcCall(): ?JsonRpcCallRequest
    {
        return $this->jsonRpcCall;
    }

    public function getResponseString(): string
    {
        return $this->responseString;
    }

    public function setResponseString(string $responseString): void
    {
        $this->responseString = $responseString;
    }

    public function setJsonRpcCallResponse(JsonRpcCallResponse $jsonRpcCallResponse): void
    {
        $this->jsonRpcCallResponse = $jsonRpcCallResponse;
    }

    public function setJsonRpcCall(?JsonRpcCallRequest $jsonRpcCall): void
    {
        $this->jsonRpcCall = $jsonRpcCall;
    }

    private ?Request $request;

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setRequest(?Request $request): void
    {
        $this->request = $request;
    }
}
