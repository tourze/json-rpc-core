<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Symfony\Component\Validator\Constraints as Assert;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * 带 Valid 验证的嵌套参数
 */
readonly class NestedWithValidationParam implements RpcParamInterface
{
    public function __construct(
        public string $title,
        #[Assert\Valid]
        public ContactParam $contact,
    ) {
    }
}
