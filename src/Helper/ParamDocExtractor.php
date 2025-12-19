<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Helper;

use Symfony\Component\Validator\Constraint;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;

/**
 * 从参数对象类中提取文档元数据
 *
 * 用于自动生成 API 文档，提取参数的名称、类型、描述、是否必填、默认值和验证约束
 */
class ParamDocExtractor
{
    /**
     * 检查类是否为支持的参数类
     */
    public static function supports(string $className): bool
    {
        if (!class_exists($className)) {
            return false;
        }

        $interfaces = class_implements($className);

        return is_subclass_of($className, RpcParamInterface::class)
            || in_array(RpcParamInterface::class, false !== $interfaces ? $interfaces : [], true);
    }

    /**
     * 从参数类中提取所有参数的文档元数据
     *
     * @param class-string $className
     * @return array<string, array{
     *     name: string,
     *     type: string,
     *     description: string,
     *     required: bool,
     *     default: mixed,
     *     constraints: array<string>
     * }>
     */
    public static function extract(string $className): array
    {
        if (!self::supports($className)) {
            return [];
        }

        $reflectionClass = new \ReflectionClass($className);
        $constructor = $reflectionClass->getConstructor();

        if (null === $constructor) {
            return [];
        }

        $result = [];
        foreach ($constructor->getParameters() as $parameter) {
            $result[$parameter->getName()] = self::extractParameterInfo($parameter);
        }

        return $result;
    }

    /**
     * 提取单个参数的元数据
     *
     * @return array{
     *     name: string,
     *     type: string,
     *     description: string,
     *     required: bool,
     *     default: mixed,
     *     constraints: array<string>
     * }
     */
    private static function extractParameterInfo(\ReflectionParameter $parameter): array
    {
        return [
            'name' => $parameter->getName(),
            'type' => self::extractType($parameter),
            'description' => self::extractDescription($parameter),
            'required' => self::isRequired($parameter),
            'default' => self::extractDefaultValue($parameter),
            'constraints' => self::extractConstraints($parameter),
        ];
    }

    /**
     * 提取参数类型
     */
    private static function extractType(\ReflectionParameter $parameter): string
    {
        $type = $parameter->getType();

        if (null === $type) {
            return 'mixed';
        }

        if ($type instanceof \ReflectionNamedType) {
            $typeName = $type->getName();
            if ($type->allowsNull() && 'null' !== $typeName) {
                return '?' . $typeName;
            }

            return $typeName;
        }

        if ($type instanceof \ReflectionUnionType) {
            $types = array_map(
                static fn (\ReflectionNamedType $t) => $t->getName(),
                $type->getTypes()
            );

            return implode('|', $types);
        }

        return 'mixed';
    }

    /**
     * 从 MethodParam 属性提取描述
     */
    private static function extractDescription(\ReflectionParameter $parameter): string
    {
        $attributes = $parameter->getAttributes(MethodParam::class);

        if ([] === $attributes) {
            return '';
        }

        $methodParam = $attributes[0]->newInstance();

        return $methodParam->description;
    }

    /**
     * 判断参数是否必填
     *
     * 必填条件：无默认值 且 MethodParam.optional = false
     */
    private static function isRequired(\ReflectionParameter $parameter): bool
    {
        // 有默认值则不是必填
        if ($parameter->isDefaultValueAvailable()) {
            return false;
        }

        // 检查 MethodParam 的 optional 标记
        $attributes = $parameter->getAttributes(MethodParam::class);
        if ([] !== $attributes) {
            $methodParam = $attributes[0]->newInstance();
            if ($methodParam->optional) {
                return false;
            }
        }

        return true;
    }

    /**
     * 提取默认值
     */
    private static function extractDefaultValue(\ReflectionParameter $parameter): mixed
    {
        if (!$parameter->isDefaultValueAvailable()) {
            return null;
        }

        return $parameter->getDefaultValue();
    }

    /**
     * 提取验证约束名称列表
     *
     * @return array<string>
     */
    private static function extractConstraints(\ReflectionParameter $parameter): array
    {
        $constraints = [];

        foreach ($parameter->getAttributes() as $attribute) {
            $attributeName = $attribute->getName();

            // 检查是否为 Symfony Validator 约束
            if (is_subclass_of($attributeName, Constraint::class)) {
                // 提取约束的短名称
                $shortName = self::getConstraintShortName($attributeName);
                $constraints[] = $shortName;
            }
        }

        return $constraints;
    }

    /**
     * 获取约束的短名称
     */
    private static function getConstraintShortName(string $fullClassName): string
    {
        $parts = explode('\\', $fullClassName);

        return end($parts);
    }
}
