<?php

namespace Tourze\JsonRPC\Core\Domain;

/**
 * Class JsonRpcMethodResolverInterface
 */
interface JsonRpcMethodResolverInterface
{
    /**
     * @return JsonRpcMethodInterface|null A valid JSON-RPC method or null if not resolved
     */
    public function resolve(string $methodName): ?JsonRpcMethodInterface;
}
