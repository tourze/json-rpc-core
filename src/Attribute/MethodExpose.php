<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Attribute;

/**
 * 声明这个是可以暴露出去的JsonRPC方法.
 */
#[\Attribute(flags: \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class MethodExpose
{
    final public const JSONRPC_METHOD_TAG = 'json_rpc_http_server.jsonrpc_method';

    public function __construct(public readonly string $method)
    {
    }
}
