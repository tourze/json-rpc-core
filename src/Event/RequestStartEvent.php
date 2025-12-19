<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

final class RequestStartEvent extends Event
{
    /**
     * @var string 接收的JsonRPC字符串
     */
    private string $payload = '';

    private ?Request $request = null;

    public function __construct(?Request $request = null, string $payload = '')
    {
        $this->request = $request;
        $this->payload = $payload;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function setRequest(?Request $request): void
    {
        $this->request = $request;
    }
}
