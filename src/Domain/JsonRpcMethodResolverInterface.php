<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Domain;

/**
 * Class JsonRpcMethodResolverInterface.
 */
interface JsonRpcMethodResolverInterface
{
    /**
     * @return JsonRpcMethodInterface|null A valid JSON-RPC method or null if not resolved
     */
    public function resolve(string $methodName): ?JsonRpcMethodInterface;

    /**
     * 获取所有已注册的方法名.
     *
     * @return string[]
     */
    public function getAllMethodNames(): array;
}
