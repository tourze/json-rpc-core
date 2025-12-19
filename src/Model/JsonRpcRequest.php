<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Model;

use Tourze\JsonRPC\Core\Exception\JsonRpcArgumentException;

final class JsonRpcRequest implements \Stringable
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
     * @var JsonRpcParams|null 调用方法所需要的结构化参数值
     */
    private ?JsonRpcParams $params = null;

    public function setParams(?JsonRpcParams $params): void
    {
        $this->params = $params;
    }

    public function getParams(): ?JsonRpcParams
    {
        return $this->params;
    }

    public function __toString(): string
    {
        $payload = [
            'jsonrpc' => $this->getJsonrpc(),
            'method' => $this->getMethod(),
        ];
        if (null !== $this->getId()) {
            $payload['id'] = $this->getId();
        }
        if (null !== $this->getParams() && $this->getParams()->count() > 0) {
            $payload['params'] = $this->getParams()->toArray();
        }

        $result = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return false !== $result ? $result : '';
    }
}
