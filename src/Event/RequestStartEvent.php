<?php

namespace Tourze\JsonRPC\Core\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class RequestStartEvent extends Event
{
    /**
     * @var string 接收的JsonRPC字符串
     */
    private string $payload;

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
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
