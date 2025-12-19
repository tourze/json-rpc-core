<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Fixtures\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 包含所有基本类型的测试用 Result 类
 */
class AllBasicTypesResult implements RpcResultInterface
{
    public function __construct(
        public readonly string $stringVal,
        public readonly int $intVal,
        public readonly float $floatVal,
        public readonly bool $boolVal,
        public readonly ?string $nullVal,
    ) {
    }

    public static function getMockResult(): ?self
    {
        return new self(
            stringVal: 'mock string',
            intVal: 123,
            floatVal: 123.45,
            boolVal: true,
            nullVal: null,
        );
    }
}
