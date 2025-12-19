<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Result;

use Tourze\JsonRPC\Core\Attribute\ResultProperty;
use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 成功响应结果
 *
 * 用于简单的成功/失败响应场景
 */
readonly class SuccessResult implements RpcResultInterface
{
    public function __construct(
        #[ResultProperty(description: '是否成功')]
        public bool $success = true,
        #[ResultProperty(description: '消息', nullable: true)]
        public ?string $message = null,
    ) {
    }

    public static function getMockResult(): ?self
    {
        return new self(
            success: true,
            message: '操作成功',
        );
    }
}
