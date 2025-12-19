<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Contracts;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;
use Tourze\JsonRPC\Core\Result\EmptyResult;
use Tourze\JsonRPC\Core\Result\SuccessResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\Result\CustomResult;

/**
 * RpcResultInterface 契约测试
 *
 * 验证标记接口的正确性和实现类的兼容性
 */
#[CoversClass(RpcResultInterface::class)]
class RpcResultInterfaceTest extends TestCase
{
    public function testInterfaceHasMockResultMethod(): void
    {
        $reflection = new \ReflectionClass(RpcResultInterface::class);
        $methods = $reflection->getMethods();

        $this->assertCount(1, $methods, 'RpcResultInterface 应该包含 getMockResult 方法');
        $this->assertSame('getMockResult', $methods[0]->getName(), '唯一的方法应该是 getMockResult');
        $this->assertTrue($methods[0]->isStatic(), 'getMockResult 方法应该是静态方法');
    }

    public function testEmptyResultImplementsInterface(): void
    {
        $result = new EmptyResult();
        $this->assertInstanceOf(RpcResultInterface::class, $result);
    }

    public function testSuccessResultImplementsInterface(): void
    {
        $result = new SuccessResult();
        $this->assertInstanceOf(RpcResultInterface::class, $result);
    }

    public function testSuccessResultDefaultValues(): void
    {
        $result = new SuccessResult();
        $this->assertTrue($result->success);
        $this->assertNull($result->message);
    }

    public function testSuccessResultWithCustomValues(): void
    {
        $result = new SuccessResult(success: true, message: '操作成功');
        $this->assertTrue($result->success);
        $this->assertSame('操作成功', $result->message);
    }

    public function testSuccessResultWithFailure(): void
    {
        $result = new SuccessResult(success: false, message: '操作失败');
        $this->assertFalse($result->success);
        $this->assertSame('操作失败', $result->message);
    }

    public function testCustomResultImplementsInterface(): void
    {
        // 创建一个自定义的 Result 类来验证接口可被正确实现
        $customResult = new CustomResult(id: 1, name: 'test');

        $this->assertInstanceOf(RpcResultInterface::class, $customResult);
        $this->assertSame(1, $customResult->id);
        $this->assertSame('test', $customResult->name);
    }
}
