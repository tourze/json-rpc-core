<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * 联系方式参数（最内层）
 */
readonly class ContactParam
{
    public function __construct(
        #[Assert\NotBlank]
        public string $phone,
        #[Assert\Email]
        public string $email,
    ) {
    }
}
