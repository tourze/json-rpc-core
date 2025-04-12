<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\JsonRpcExceptionInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcInternalErrorException;
use Tourze\JsonRPC\Core\Exception\JsonRpcInvalidParamsException;
use Tourze\JsonRPC\Core\Exception\JsonRpcInvalidRequestException;
use Tourze\JsonRPC\Core\Exception\JsonRpcMethodNotFoundException;
use Tourze\JsonRPC\Core\Exception\JsonRpcParseErrorException;

class JsonRpcSpecificExceptionsTest extends TestCase
{
    public function testParseErrorException(): void
    {
        $content = 'Invalid JSON content';
        $parseErrorCode = 10;
        $parseErrorMessage = 'Syntax error';
        $exception = new JsonRpcParseErrorException($content, $parseErrorCode, $parseErrorMessage);

        $this->assertInstanceOf(JsonRpcExceptionInterface::class, $exception);
        $this->assertEquals(-32700, $exception->getCode());
        $this->assertEquals(-32700, $exception->getErrorCode());
        $this->assertEquals('Parse error', $exception->getMessage());
        $this->assertEquals('Parse error', $exception->getErrorMessage());
        $this->assertEquals($content, $exception->getContent());
        $this->assertEquals($parseErrorCode, $exception->getParseErrorCode());
        $this->assertEquals($parseErrorMessage, $exception->getParseErrorMessage());
    }

    public function testInvalidRequestException(): void
    {
        $content = ['jsonrpc' => '2.0', 'method' => null];
        $description = 'Method is missing or invalid';
        $exception = new JsonRpcInvalidRequestException($content, $description);

        $this->assertInstanceOf(JsonRpcExceptionInterface::class, $exception);
        $this->assertEquals(-32600, $exception->getCode());
        $this->assertEquals(-32600, $exception->getErrorCode());
        $this->assertEquals("Invalid request: {$description}", $exception->getMessage());
        $this->assertEquals("Invalid request: {$description}", $exception->getErrorMessage());
        $this->assertSame($content, $exception->getContent());
        $this->assertEquals($description, $exception->getDescription());
    }

    public function testMethodNotFoundException(): void
    {
        $methodName = 'non_existent_method';
        $context = ['available_methods' => ['method1', 'method2']];
        $exception = new JsonRpcMethodNotFoundException($methodName, $context);

        $this->assertInstanceOf(JsonRpcExceptionInterface::class, $exception);
        $this->assertEquals(-32601, $exception->getCode());
        $this->assertEquals(-32601, $exception->getErrorCode());
        $this->assertEquals('Method not found', $exception->getMessage());
        $this->assertEquals('Method not found', $exception->getErrorMessage());
        $this->assertEquals($methodName, $exception->getMethodName());
        $this->assertEquals($context, $exception->getContext());
    }

    public function testInvalidParamsException(): void
    {
        $violationMessageList = [
            ['path' => 'user_id', 'message' => 'must be an integer'],
            ['path' => 'email', 'message' => 'must be a valid email']
        ];
        $exception = new JsonRpcInvalidParamsException($violationMessageList);

        $this->assertInstanceOf(JsonRpcExceptionInterface::class, $exception);
        $this->assertEquals(-32602, $exception->getCode());
        $this->assertEquals(-32602, $exception->getErrorCode());
        $this->assertEquals('Invalid params', $exception->getMessage());
        $this->assertEquals('Invalid params', $exception->getErrorMessage());
        $this->assertEquals(['violations' => $violationMessageList], $exception->getErrorData());
    }

    public function testInternalErrorException(): void
    {
        $previousException = new \RuntimeException('Some internal error');
        $exception = new JsonRpcInternalErrorException($previousException);

        $this->assertInstanceOf(JsonRpcExceptionInterface::class, $exception);
        $this->assertEquals(-32603, $exception->getCode());
        $this->assertEquals(-32603, $exception->getErrorCode());
        $this->assertStringContainsString('Internal error', $exception->getMessage());
        $this->assertStringContainsString('Internal error', $exception->getErrorMessage());
    }
}
