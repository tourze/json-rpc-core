<?php

namespace Tourze\JsonRPC\Core\Attribute;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * 声明这个是可以暴露出去的JsonRPC方法
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class MethodExpose extends AutoconfigureTag
{
    final public const JSONRPC_METHOD_TAG = 'json_rpc_http_server.jsonrpc_method';

    public function __construct(?string $method = null)
    {
        if (null === $method) {
            throw new \InvalidArgumentException('method参数不能为空');
        }

        parent::__construct(self::JSONRPC_METHOD_TAG, ['method' => $method]);
    }
}
