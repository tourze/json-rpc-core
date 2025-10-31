<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Model;

class JsonRpcCallRequest
{
    /** @var (JsonRpcRequest|\Exception)[] */
    private array $itemList = [];

    public function __construct(private readonly bool $isBatch = false)
    {
    }

    public function addRequestItem(JsonRpcRequest $item): void
    {
        $this->itemList[] = $item;
    }

    public function addExceptionItem(\Exception $item): void
    {
        $this->itemList[] = $item;
    }

    public function isBatch(): bool
    {
        return $this->isBatch;
    }

    /**
     * @return (JsonRpcRequest|\Exception)[]
     */
    public function getItemList(): array
    {
        return $this->itemList;
    }
}
