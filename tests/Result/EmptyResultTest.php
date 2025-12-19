<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Result;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;
use Tourze\JsonRPC\Core\Result\EmptyResult;

/**
 * EmptyResult 测试
 */
#[CoversClass(EmptyResult::class)]
class EmptyResultTest extends TestCase
{
    public function testImplementsRpcResultInterface(): void
    {
        $result = new EmptyResult();
        $this->assertInstanceOf(RpcResultInterface::class, $result);
    }

    public function testIsReadonlyClass(): void
    {
        $reflection = new \ReflectionClass(EmptyResult::class);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testHasNoProperties(): void
    {
        $reflection = new \ReflectionClass(EmptyResult::class);
        $this->assertCount(0, $reflection->getProperties());
    }
}
