<?php

namespace Tourze\JsonRPC\Core\Event;

use Tourze\JsonRPC\Core\Model\JsonRpcParams;

/**
 * 在JsonRPC方法执行后触发
 */
class AfterMethodApplyEvent extends MethodInterruptEvent
{
    private JsonRpcParams $params;

    public function getParams(): JsonRpcParams
    {
        return $this->params;
    }

    public function setParams(JsonRpcParams $params): void
    {
        $this->params = $params;
    }

    private mixed $result = null;

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function setResult(mixed $result): void
    {
        $this->result = $result;
    }

    private string $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
