<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Model;

use Tourze\JsonRPC\Core\Exception\JsonRpcArgumentException;
use Tourze\JsonRPC\Core\Exception\JsonRpcExceptionInterface;

class JsonRpcResponse
{
    private mixed $result = null;

    private ?JsonRpcExceptionInterface $error = null;

    private string $jsonrpc = '2.0';

    public function getJsonrpc(): string
    {
        return $this->jsonrpc;
    }

    public function setJsonrpc(string $jsonrpc): void
    {
        $this->jsonrpc = $jsonrpc;
    }

    /**
     * @var int|string|null 请求ID
     */
    private int|string|null $id = null;

    public function setId(mixed $id): void
    {
        if (!is_string($id) && !is_int($id)) {
            throw new JsonRpcArgumentException('Id must be either an int or a string');
        }

        $this->id = $id;
    }

    public function getId(): int|string|null
    {
        return $this->id;
    }

    private bool $isNotification = false;

    public function setResult(mixed $result): void
    {
        $this->result = $result;
    }

    public function setError(JsonRpcExceptionInterface $error): void
    {
        $this->error = $error;
    }

    public function setIsNotification(bool $isNotification): void
    {
        $this->isNotification = $isNotification;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function getError(): ?JsonRpcExceptionInterface
    {
        return $this->error;
    }

    public function isNotification(): bool
    {
        return $this->isNotification;
    }
}
