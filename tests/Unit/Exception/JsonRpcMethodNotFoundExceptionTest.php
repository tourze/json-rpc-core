<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\BacktraceHelper\ContextAwareInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Exception\JsonRpcExceptionInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcMethodNotFoundException;

class JsonRpcMethodNotFoundExceptionTest extends TestCase
{
    public function testExceptionCreation(): void
    {
        $methodName = 'testMethod';
        $context = ['request_id' => '123', 'user_id' => '456'];
        
        $exception = new JsonRpcMethodNotFoundException($methodName, $context);

        $this->assertInstanceOf(JsonRpcExceptionInterface::class, $exception);
        $this->assertInstanceOf(JsonRpcException::class, $exception);
        $this->assertInstanceOf(ContextAwareInterface::class, $exception);
        $this->assertEquals(JsonRpcMethodNotFoundException::CODE, $exception->getCode());
        $this->assertEquals(-32601, $exception->getCode());
        $this->assertEquals('Method not found', $exception->getMessage());
        $this->assertEquals($methodName, $exception->getMethodName());
        $this->assertEquals($context, $exception->getContext());
    }

    public function testExceptionCode(): void
    {
        $exception = new JsonRpcMethodNotFoundException('test', []);

        $this->assertEquals(-32601, $exception->getCode());
        $this->assertEquals(-32601, JsonRpcMethodNotFoundException::CODE);
    }

    public function testExceptionInheritsFromJsonRpcException(): void
    {
        $exception = new JsonRpcMethodNotFoundException('test', []);

        $this->assertInstanceOf(JsonRpcException::class, $exception);
        $this->assertEquals($exception->getErrorCode(), $exception->getCode());
        $this->assertEquals($exception->getErrorMessage(), $exception->getMessage());
    }

    public function testContextHandling(): void
    {
        $methodName = 'testMethod';
        $initialContext = ['key1' => 'value1'];
        $newContext = ['key2' => 'value2'];
        
        $exception = new JsonRpcMethodNotFoundException($methodName, $initialContext);

        $this->assertEquals($initialContext, $exception->getContext());
        
        $exception->setContext($newContext);
        $this->assertEquals($newContext, $exception->getContext());
    }

    public function testGetMethodName(): void
    {
        $methodName = 'someMethod';
        $exception = new JsonRpcMethodNotFoundException($methodName, []);

        $this->assertEquals($methodName, $exception->getMethodName());
    }

    public function testImplementsContextAwareInterface(): void
    {
        $exception = new JsonRpcMethodNotFoundException('test', []);

        $this->assertInstanceOf(ContextAwareInterface::class, $exception);
    }
}