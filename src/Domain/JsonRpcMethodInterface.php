<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Domain;

use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * 方法定义.
 */
interface JsonRpcMethodInterface
{
    /**
     * @throws \Exception       In case of failure. Code and message will be used to generate custom JSON-RPC error
     * @throws JsonRpcException In case of failure. Exception will be re-thrown as is
     */
    public function __invoke(JsonRpcRequest $request): mixed;

    /**
     * apply成功后，返回结果
     * execute方法将要废弃，请使用apply方法.
     *
     * @return array<string, mixed>
     */
    public function execute(): array;
}
