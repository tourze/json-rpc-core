<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures;

use Symfony\Component\Validator\Constraints as Assert;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * 测试用的简单参数类（文档提取测试专用）
 */
readonly class DocExtractorSimpleParam implements RpcParamInterface
{
    public function __construct(
        #[MethodParam(description: '用户ID')]
        #[Assert\NotBlank]
        public string $userId,

        #[MethodParam(description: '用户名', optional: true)]
        public ?string $username = null,

        #[MethodParam(description: '年龄')]
        #[Assert\Range(min: 0, max: 150)]
        public int $age = 18,

        #[MethodParam(description: '邮箱')]
        #[Assert\Email]
        public string $email = 'default@example.com',
    ) {
    }
}
