<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

/**
 * 客户参数（中间层）
 */
readonly class CustomerParam
{
    public function __construct(
        public string $name,
        public ContactParam $contact,
    ) {
    }
}
