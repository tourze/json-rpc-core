<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Helper;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;

/**
 * Result 对象序列化辅助类
 *
 * 将 RpcResultInterface 对象序列化为数组，供框架层使用
 * 框架层（如 RequestHandler 或 Endpoint）在调用 __invoke 后使用此类进行序列化
 */
class ResultObjectSerializer
{
    /**
     * 序列化深度限制，防止栈溢出
     */
    private const MAX_DEPTH = 32;

    /**
     * 将 Result 对象序列化为数组
     *
     * @param RpcResultInterface $result Result 对象
     *
     * @return array<string, mixed> 序列化后的数组
     *
     * @throws ApiException 当序列化失败时（如循环引用）
     */
    public function serialize(RpcResultInterface $result): array
    {
        // JsonSerializable 优先处理（如 ArrayResult）
        if ($result instanceof \JsonSerializable) {
            $jsonData = $result->jsonSerialize();
            if (is_array($jsonData)) {
                return $this->serializeArray($jsonData, [], 0);
            }

            // 非数组类型不应该作为顶层结果
            throw new ApiException('JsonSerializable::jsonSerialize() 必须返回数组');
        }

        return $this->serializeObject($result, [], 0);
    }

    /**
     * 递归序列化对象
     *
     * @param object $object 要序列化的对象
     * @param array<int, object> $visited 已访问对象列表（用于检测循环引用）
     * @param int $depth 当前深度
     *
     * @return array<string, mixed>
     *
     * @throws ApiException 当序列化失败时
     */
    private function serializeObject(object $object, array $visited, int $depth): array
    {
        // 检查深度限制
        if ($depth > self::MAX_DEPTH) {
            throw new ApiException('Result 对象嵌套深度超过限制');
        }

        // 检查循环引用
        $objectId = spl_object_id($object);
        if (in_array($objectId, array_map('spl_object_id', $visited), true)) {
            throw new ApiException('Result 对象存在循环引用');
        }
        $visited[] = $object;

        $result = [];
        $reflection = new \ReflectionObject($object);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();
            $value = $property->getValue($object);

            $result[$name] = $this->serializeValue($value, $visited, $depth + 1);
        }

        return $result;
    }

    /**
     * 序列化单个值
     *
     * @param mixed $value 要序列化的值
     * @param array<int, object> $visited 已访问对象列表
     * @param int $depth 当前深度
     *
     * @return mixed 序列化后的值
     *
     * @throws ApiException 当序列化失败时
     */
    private function serializeValue(mixed $value, array $visited, int $depth): mixed
    {
        // null 直接返回
        if (null === $value) {
            return null;
        }

        // 标量类型直接返回
        if (is_scalar($value)) {
            return $value;
        }

        // 数组递归处理
        if (is_array($value)) {
            return $this->serializeArray($value, $visited, $depth);
        }

        // 对象处理
        if (is_object($value)) {
            return $this->serializeObjectValue($value, $visited, $depth);
        }

        // 不支持的类型（如 resource）
        throw new ApiException(sprintf(
            'Result 对象包含不支持序列化的类型: %s',
            get_debug_type($value)
        ));
    }

    /**
     * 序列化数组
     *
     * @param array<mixed> $array
     * @param array<int, object> $visited
     * @param int $depth
     *
     * @return array<mixed>
     *
     * @throws ApiException
     */
    private function serializeArray(array $array, array $visited, int $depth): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $result[$key] = $this->serializeValue($value, $visited, $depth);
        }

        return $result;
    }

    /**
     * 序列化对象值
     *
     * @param object $value
     * @param array<int, object> $visited
     * @param int $depth
     *
     * @return mixed
     *
     * @throws ApiException
     */
    private function serializeObjectValue(object $value, array $visited, int $depth): mixed
    {
        // JsonSerializable 优先处理（如 ArrayResult）
        if ($value instanceof \JsonSerializable) {
            $jsonData = $value->jsonSerialize();
            if (is_array($jsonData)) {
                return $this->serializeArray($jsonData, $visited, $depth);
            }

            return $this->serializeValue($jsonData, $visited, $depth);
        }

        // RpcResultInterface 递归序列化
        if ($value instanceof RpcResultInterface) {
            return $this->serializeObject($value, $visited, $depth);
        }

        // DateTime 序列化为 ISO 8601 字符串
        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        // Backed Enum 序列化为其值
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        // Unit Enum 序列化为其名称
        if ($value instanceof \UnitEnum) {
            return $value->name;
        }

        // 其他对象尝试作为普通对象序列化
        return $this->serializeObject($value, $visited, $depth);
    }
}
