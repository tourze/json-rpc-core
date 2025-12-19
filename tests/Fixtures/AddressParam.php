<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

/**
 * 地址参数（嵌套子对象）
 */
readonly class AddressParam
{
    public function __construct(
        public string $street,
        public string $city,
    ) {
    }
}
