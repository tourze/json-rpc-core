<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Model;

use Symfony\Component\HttpFoundation\ParameterBag;
use Tourze\Arrayable\Arrayable;

/**
 * 通用的请求对象
 */
class JsonRpcParams extends ParameterBag implements Arrayable
{
    public function toArray(): array
    {
        return $this->all();
    }
}
