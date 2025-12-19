<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Contracts;

use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;

/**
 * Procedure 参数类注册表接口
 *
 * 在容器编译时收集 Procedure -> Param 类的映射关系，
 * 避免运行时反射带来的性能开销和类型检测问题。
 */
interface ProcedureParamRegistryInterface
{
    /**
     * 获取指定 Procedure 类关联的 Param 类名
     *
     * @param class-string<JsonRpcMethodInterface> $procedureClass
     *
     * @return class-string<RpcParamInterface>|null 如果未注册返回 null
     */
    public function getParamClass(string $procedureClass): ?string;

    /**
     * 检查指定 Procedure 类是否已注册
     *
     * @param class-string<JsonRpcMethodInterface> $procedureClass
     */
    public function has(string $procedureClass): bool;

    /**
     * 获取所有 Procedure -> Param 的映射
     *
     * @return array<class-string<JsonRpcMethodInterface>, class-string<RpcParamInterface>>
     */
    public function getAll(): array;
}
