<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Domain;

use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * JsonRPC 方法接口
 *
 * 纯参数对象模式：
 * - execute 方法必须接收一个 RpcParamInterface 类型的参数
 * - execute 方法必须返回一个 RpcResultInterface 类型的结果
 * - 框架自动将请求数据反序列化为参数对象并注入
 * - 框架自动将返回的 Result 对象序列化为 JSON 响应
 */
interface JsonRpcMethodInterface
{
    /**
     * @throws \Exception       In case of failure. Code and message will be used to generate custom JSON-RPC error
     * @throws JsonRpcException In case of failure. Exception will be re-thrown as is
     */
    public function __invoke(JsonRpcRequest $request): RpcResultInterface;

    /**
     * 执行 Procedure 逻辑
     *
     * @param RpcParamInterface $param 参数对象（由框架自动创建和注入）
     *
     * @return RpcResultInterface 执行结果（由框架自动序列化为 JSON）
     */
    public function execute(RpcParamInterface $param): RpcResultInterface;
}
