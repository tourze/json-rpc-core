<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

/**
 * 测试用枚举
 */
enum TestStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
