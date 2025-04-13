<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\BacktraceHelper\ContextAwareInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Exception\JsonRpcExceptionInterface;

/**
 * 测试ApiException异常类
 */
class ApiExceptionTest extends TestCase
{
    /**
     * 测试使用字符串消息创建异常
     */
    public function testCreateWithStringMessage(): void
    {
        $message = '测试错误信息';
        $code = 1001;
        $data = ['detail' => '详细信息'];

        $exception = new ApiException($message, $code, $data);

        $this->assertInstanceOf(JsonRpcExceptionInterface::class, $exception);
        $this->assertInstanceOf(ContextAwareInterface::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($data, $exception->getErrorData());
    }

    /**
     * 测试使用数组形式的消息和代码创建异常
     */
    public function testCreateWithArrayMessageAndCode(): void
    {
        $code = 1002;
        $message = '数组形式的错误信息';
        $data = ['detail' => '详细信息'];

        $exception = new ApiException([$code, $message], 0, $data);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertEquals($data, $exception->getErrorData());
    }

    /**
     * 测试带有前置异常的创建
     */
    public function testCreateWithPreviousException(): void
    {
        $previous = new \RuntimeException('前置异常');
        $exception = new ApiException('API错误', 1003, [], $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    /**
     * 测试ContextAwareInterface接口实现
     */
    public function testContextAwareInterface(): void
    {
        $data = ['key1' => 'value1'];
        $exception = new ApiException('测试');

        // 初始化为空数组
        $this->assertEquals([], $exception->getContext());

        // 设置上下文
        $exception->setContext($data);
        $this->assertEquals($data, $exception->getContext());

        // 上下文应该与errorData相同
        $this->assertEquals($data, $exception->getErrorData());

        // 修改errorData也应该修改上下文
        $newData = ['key2' => 'value2'];
        $exception->setErrorData($newData);
        $this->assertEquals($newData, $exception->getContext());
    }
}
