<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Model;

use Symfony\Component\HttpFoundation\ParameterBag;
use Tourze\Arrayable\Arrayable;

/**
 * 通用的请求对象
 *
 * @implements Arrayable<string, mixed>
 */
class JsonRpcParams extends ParameterBag implements Arrayable
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        /** @var array<string, mixed> */
        return $this->all();
    }
}
