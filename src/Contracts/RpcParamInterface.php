<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Contracts;

use Tourze\JsonRPC\Core\Helper\ParamObjectFactory;

/**
 * 标记接口：所有 JsonRPC Procedure 参数类必须实现此接口
 *
 * 实现此接口的类将被框架识别为参数对象，支持：
 * - 自动反序列化 JSON 请求参数到参数对象
 * - 自动执行 Symfony Validator 验证
 * - 自动注入到 Procedure 的 execute 方法
 *
 * 命名规范：{ProcedureName}Param，如 GetCatalogDetailParam
 * 文件位置：packages/{bundle}/src/Param/{ProcedureName}Param.php
 *
 * @see \Tourze\JsonRPC\Core\Procedure\BaseProcedure::detectParamClass()
 * @see ParamObjectFactory
 */
interface RpcParamInterface
{
    // 标记接口，无方法定义
}
