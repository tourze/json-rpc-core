<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Symfony\Component\Validator\Constraints\Email;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * 带验证的测试参数类
 */
readonly class ValidatedTestParam implements RpcParamInterface
{
    public function __construct(
        #[Email]
        public string $email,
    ) {
    }
}
