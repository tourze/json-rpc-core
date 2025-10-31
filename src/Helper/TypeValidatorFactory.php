<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Helper;

use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Type;

class TypeValidatorFactory
{
    public static function createFromReflectionType(\ReflectionType $type): Type|AtLeastOneOf|null
    {
        if ($type instanceof \ReflectionUnionType) {
            return self::createFromUnionType($type);
        }

        if ($type instanceof \ReflectionNamedType) {
            return self::createFromNamedType($type);
        }

        return null;
    }

    private static function createFromUnionType(\ReflectionUnionType $type): AtLeastOneOf
    {
        $validators = [];
        foreach ($type->getTypes() as $subType) {
            $validator = self::createFromReflectionType($subType);
            if (null !== $validator) {
                $validators[] = $validator;
            }
        }

        return new AtLeastOneOf($validators);
    }

    private static function createFromNamedType(\ReflectionNamedType $type): ?Type
    {
        if (!$type->isBuiltin()) {
            return null;
        }

        return self::createFromTypeName($type->getName());
    }

    public static function createFromTypeName(string $typeName): ?Type
    {
        return match ($typeName) {
            'null' => new Type('null'),
            'string' => new Type('string'),
            'bool', 'boolean' => new Type('bool'),
            'float', 'double' => new Type('float'),
            'int', 'integer' => new Type('integer'),
            'array' => new Type('array'),
            default => null,
        };
    }

    public static function makeTypeCompatible(Type|AtLeastOneOf $type): AtLeastOneOf|Type
    {
        if ($type instanceof Type) {
            if (in_array($type->type, ['int', 'integer', 'string', 'float', 'double'], true)) {
                return new AtLeastOneOf([
                    new Type('integer'),
                    new Type('string'),
                    new Type('float'),
                ]);
            }

            if (in_array($type->type, ['boolean', 'bool'], true)) {
                return new AtLeastOneOf([
                    new Type('string'),
                    new Type('integer'),
                    new Type('bool'),
                ]);
            }
        }

        return $type;
    }
}
