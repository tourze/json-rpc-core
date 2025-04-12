<?php

namespace Tourze\JsonRPC\Core\Model;

class JsonRpcCallRequest
{
    /** @var (JsonRpcRequest|\Exception)[] */
    private array $itemList = [];

    public function __construct(private readonly bool $isBatch = false)
    {
    }

    public function addRequestItem(JsonRpcRequest $item): self
    {
        $this->itemList[] = $item;

        return $this;
    }

    public function addExceptionItem(\Exception $item): self
    {
        $this->itemList[] = $item;

        return $this;
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
