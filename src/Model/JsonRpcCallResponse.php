<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Model;

class JsonRpcCallResponse
{
    /** @var JsonRpcResponse[] */
    private array $responseList = [];

    public function __construct(private readonly bool $isBatch = false)
    {
    }

    public function addResponse(JsonRpcResponse $response): void
    {
        $this->responseList[] = $response;
    }

    public function isBatch(): bool
    {
        return $this->isBatch;
    }

    /**
     * @return JsonRpcResponse[]
     */
    public function getResponseList(): array
    {
        return $this->responseList;
    }
}
