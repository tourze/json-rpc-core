<?php

namespace Tourze\JsonRPC\Core\Exception;

use Tourze\BacktraceHelper\ContextAwareInterface;

/**
 * Class JsonRpcMethodNotFoundException
 */
class JsonRpcMethodNotFoundException extends JsonRpcException implements ContextAwareInterface
{
    final public const CODE = -32601;

    public function __construct(private readonly string $methodName, array $context)
    {
        parent::__construct(self::CODE, 'Method not found');
        $this->setContext($context);
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    private array $context = [];

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }
}
