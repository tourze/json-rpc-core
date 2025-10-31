<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Exception;

use Tourze\BacktraceHelper\ContextAwareInterface;

/**
 * JSON-RPC 方法未找到异常.
 */
class JsonRpcMethodNotFoundException extends JsonRpcException implements ContextAwareInterface
{
    final public const CODE = -32601;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(private readonly string $methodName, array $context)
    {
        parent::__construct(self::CODE, 'Method not found');
        $this->setContext($context);
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    /**
     * @var array<string, mixed>
     */
    private array $context = [];

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @param array<string, mixed> $context
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }
}
