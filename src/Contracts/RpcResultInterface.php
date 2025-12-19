<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Contracts;

/**
 * 标记接口：所有 JsonRPC Procedure 返回值类必须实现此接口
 *
 * 实现此接口的类将被框架识别为返回值对象，支持：
 * - 自动序列化为 JSON 响应
 * - 类型安全的返回值约束
 * - IDE 自动补全和静态分析
 * - Mock 数据生成（通过 getMockResult 方法）
 *
 * 命名规范：{ProcedureName}Result，如 GetProductListWithFilterResult
 * 文件位置：packages/{bundle}/src/Result/{ProcedureName}Result.php
 *
 * @see RpcParamInterface 对称的输入接口
 * @see \Tourze\JsonRPC\Core\Procedure\BaseProcedure::execute() 返回类型约束
 */
interface RpcResultInterface
{
    /**
     * 返回 Mock 数据
     *
     * 用于文档生成、测试等场景，返回该 Result 类型的示例数据
     *
     * @return static|null 返回 Result 实例，如果不需要 Mock 数据则返回 null
     */
    public static function getMockResult(): ?self;
}
