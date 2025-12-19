<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

readonly class StringArrayParam implements RpcParamInterface
{
    /**
     * @param string[] $tags
     */
    public function __construct(
        public string $title,
        public array $tags,
    ) {
    }
}
