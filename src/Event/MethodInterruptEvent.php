<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;

/**
 * 通用的方法拦截事件.
 */
abstract class MethodInterruptEvent extends Event
{
    private JsonRpcMethodInterface $method;

    public function getMethod(): JsonRpcMethodInterface
    {
        return $this->method;
    }

    public function setMethod(JsonRpcMethodInterface $method): void
    {
        $this->method = $method;
    }
}
