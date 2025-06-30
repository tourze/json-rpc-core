<?php

namespace Tourze\JsonRPC\Core\Model;

use Tourze\JsonRPC\Core\Exception\JsonRpcExceptionInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcArgumentException;

class JsonRpcResponse
{
    private $result;

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
     * @var string|null 请求ID
     */
    private $id;

    public function setId(mixed $id): self
    {
        if (!is_string($id) && !is_int($id)) {
            throw new JsonRpcArgumentException('Id must be either an int or a string');
        }

        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    private bool $isNotification = false;

    public function setResult(mixed $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function setError(JsonRpcExceptionInterface $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function setIsNotification(bool $isNotification): self
    {
        $this->isNotification = $isNotification;

        return $this;
    }

    public function getResult()
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
