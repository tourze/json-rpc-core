<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\JsonRPC\Core\Exception\AccessDeniedException;
use Tourze\JsonRPC\Core\Exception\JsonRpcExceptionInterface;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * 测试AccessDeniedException异常类.
 *
 * @internal
 */
#[CoversClass(AccessDeniedException::class)]
final class AccessDeniedExceptionTest extends AbstractExceptionTestCase
{
    /**
     * 测试异常的基本属性.
     */
    public function testBasicProperties(): void
    {
        $exception = new AccessDeniedException();

        $this->assertInstanceOf(JsonRpcExceptionInterface::class, $exception);
        $this->assertEquals(AccessDeniedException::ERROR_CODE, $exception->getCode());
        $this->assertEquals(AccessDeniedException::ERROR_MESSAGE, $exception->getMessage());
        $this->assertEquals([], $exception->getErrorData());
    }

    /**
     * 测试带有前置异常的创建.
     */
    public function testWithPreviousException(): void
    {
        $previousMessage = '权限验证失败';
        $previousException = new \Exception($previousMessage);

        $exception = new AccessDeniedException($previousException);

        $this->assertEquals(AccessDeniedException::ERROR_CODE, $exception->getCode());
        $this->assertEquals(AccessDeniedException::ERROR_MESSAGE, $exception->getMessage());

        $errorData = $exception->getErrorData();
        $this->assertArrayHasKey(AccessDeniedException::DATA_PREVIOUS_KEY, $errorData);
        $this->assertEquals($previousMessage, $errorData[AccessDeniedException::DATA_PREVIOUS_KEY]);
    }

    /**
     * 测试常量值
     */
    public function testConstantValues(): void
    {
        $this->assertEquals(-996, AccessDeniedException::ERROR_CODE);
        $this->assertEquals('当前用户未获得访问授权!', AccessDeniedException::ERROR_MESSAGE);
        $this->assertEquals('previous', AccessDeniedException::DATA_PREVIOUS_KEY);
    }
}
