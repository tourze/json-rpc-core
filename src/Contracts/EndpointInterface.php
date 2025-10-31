<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Contracts;

use Symfony\Component\HttpFoundation\Request;

interface EndpointInterface
{
    public function index(string $payload, ?Request $request = null): string;
}
