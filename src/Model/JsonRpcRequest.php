<?php

namespace Tourze\JsonRPC\Core\Model;

use Tourze\JsonRPC\Core\Exception\JsonRpcArgumentException;

class JsonRpcRequest
{
    /**
     * @var string JSONRPC协议版本
     */
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

    public function setId(mixed $id): self
    {
        if (!is_string($id) && !is_int($id)) {
            throw new JsonRpcArgumentException('Id must be either an int or a string');
        }

        $this->id = $id;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function isNotification(): bool
    {
        return null === $this->id;
    }

    /**
     * @var string 执行方法
     */
    private string $method;

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @var JsonRpcParams 调用方法所需要的结构化参数值
     */
    private JsonRpcParams $params;

    public function setParams(JsonRpcParams $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function getParams(): JsonRpcParams
    {
        return $this->params;
    }
}
