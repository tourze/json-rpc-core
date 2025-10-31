<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Helper;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;

class PropertyConstraintExtractor
{
    /**
     * @return Constraint|Collection|array<Constraint>|null
     */
    public static function extractConstraint(\ReflectionProperty $property): Constraint|Collection|array|null
    {
        if (self::isEnumProperty($property)) {
            return self::extractEnumConstraint($property);
        }

        $constraints = self::extractAllConstraints($property);

        if ([] === $constraints) {
            return null;
        }

        if (1 === count($constraints)) {
            return array_shift($constraints);
        }

        return self::extractTypeConstraintOnly($property);
    }

    private static function isEnumProperty(\ReflectionProperty $property): bool
    {
        $type = $property->getType();
        if (!($type instanceof \ReflectionNamedType)) {
            return false;
        }

        $name = $type->getName();

        return class_exists($name) && is_subclass_of($name, \BackedEnum::class);
    }

    private static function extractEnumConstraint(\ReflectionProperty $property): ?Constraint
    {
        $type = $property->getType();
        if (!($type instanceof \ReflectionNamedType)) {
            return null;
        }

        $name = $type->getName();
        if (!class_exists($name) || !is_subclass_of($name, \BackedEnum::class)) {
            return null;
        }

        /** @var class-string<\BackedEnum> $name */
        $reflectionEnum = new \ReflectionEnum($name);
        $backingType = $reflectionEnum->getBackingType();

        if (null === $backingType) {
            return null;
        }

        return TypeValidatorFactory::createFromTypeName($backingType->getName());
    }

    /**
     * @return array<Constraint>
     */
    private static function extractAllConstraints(\ReflectionProperty $property): array
    {
        $constraints = [];

        $typeConstraint = self::extractTypeConstraint($property);
        if ($typeConstraint instanceof \Symfony\Component\Validator\Constraints\Type || $typeConstraint instanceof \Symfony\Component\Validator\Constraints\AtLeastOneOf) {
            $constraints[] = TypeValidatorFactory::makeTypeCompatible($typeConstraint);
        }

        $attributeConstraints = self::extractAttributeConstraints($property);

        return array_merge($constraints, $attributeConstraints);
    }

    private static function extractTypeConstraint(\ReflectionProperty $property): mixed
    {
        $type = $property->getType();
        if (null === $type) {
            return null;
        }

        return TypeValidatorFactory::createFromReflectionType($type);
    }

    /**
     * @return array<Constraint>
     */
    private static function extractAttributeConstraints(\ReflectionProperty $property): array
    {
        $reflectionType = $property->getType();
        if (!($reflectionType instanceof \ReflectionNamedType) || !$reflectionType->isBuiltin()) {
            return [];
        }

        $constraints = [];
        foreach ($property->getAttributes() as $attribute) {
            if (is_subclass_of($attribute->getName(), Constraint::class)) {
                $instance = $attribute->newInstance();
                if ($instance instanceof Constraint) {
                    $constraints[] = $instance;
                }
            }
        }

        return $constraints;
    }

    /**
     * @return Constraint|null
     */
    private static function extractTypeConstraintOnly(\ReflectionProperty $property): ?Constraint
    {
        $typeConstraint = self::extractTypeConstraint($property);
        if ($typeConstraint instanceof \Symfony\Component\Validator\Constraints\Type || $typeConstraint instanceof \Symfony\Component\Validator\Constraints\AtLeastOneOf) {
            return TypeValidatorFactory::makeTypeCompatible($typeConstraint);
        }

        return null;
    }
}
