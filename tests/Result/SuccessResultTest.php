<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Result;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;
use Tourze\JsonRPC\Core\Result\SuccessResult;

/**
 * SuccessResult 测试
 */
#[CoversClass(SuccessResult::class)]
class SuccessResultTest extends TestCase
{
    public function testImplementsRpcResultInterface(): void
    {
        $result = new SuccessResult();
        $this->assertInstanceOf(RpcResultInterface::class, $result);
    }

    public function testIsReadonlyClass(): void
    {
        $reflection = new \ReflectionClass(SuccessResult::class);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function testDefaultValues(): void
    {
        $result = new SuccessResult();
        $this->assertTrue($result->success);
        $this->assertNull($result->message);
    }

    public function testWithMessage(): void
    {
        $result = new SuccessResult(message: '操作成功');
        $this->assertTrue($result->success);
        $this->assertSame('操作成功', $result->message);
    }

    public function testWithFailure(): void
    {
        $result = new SuccessResult(success: false, message: '操作失败');
        $this->assertFalse($result->success);
        $this->assertSame('操作失败', $result->message);
    }
}
