<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Contracts;

use Tourze\JsonRPC\Core\Exception\ApiException;

/**
 * 参数对象工厂接口
 *
 * 负责将原始请求数据转换为参数对象实例：
 * 1. 将数组数据反序列化为参数对象
 * 2. 使用 Symfony Validator 验证参数对象
 * 3. 返回参数对象或抛出验证异常
 */
interface ParamObjectFactoryInterface
{
    /**
     * 创建参数对象实例
     *
     * @template T of RpcParamInterface
     *
     * @param class-string<T>      $className 参数类名
     * @param array<string, mixed> $data      原始请求数据
     *
     * @return T 参数对象实例
     *
     * @throws ApiException 反序列化或验证失败
     */
    public function create(string $className, array $data): RpcParamInterface;

    /**
     * 检查类是否实现 RpcParamInterface
     *
     * @param class-string $className 类名
     */
    public function supports(string $className): bool;
}
