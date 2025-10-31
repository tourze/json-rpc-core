<?php

namespace Tourze\JsonRPC\Core\Tests\Helper;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Type;
use Tourze\JsonRPC\Core\Helper\TypeValidatorFactory;

/**
 * @internal
 */
#[CoversClass(TypeValidatorFactory::class)]
final class TypeValidatorFactoryTest extends TestCase
{
    public function testCreateFromReflectionType(): void
    {
        $reflection = new \ReflectionClass(new class {
            public string $stringProperty;
        });

        $property = $reflection->getProperty('stringProperty');
        $type = $property->getType();
        $this->assertNotNull($type);

        $result = TypeValidatorFactory::createFromReflectionType($type);

        $this->assertInstanceOf(Type::class, $result);
        $this->assertEquals('string', $result->type);
    }

    public function testCreateFromTypeName(): void
    {
        $stringType = TypeValidatorFactory::createFromTypeName('string');
        $this->assertInstanceOf(Type::class, $stringType);
        $this->assertEquals('string', $stringType->type);

        $intType = TypeValidatorFactory::createFromTypeName('int');
        $this->assertInstanceOf(Type::class, $intType);
        $this->assertEquals('integer', $intType->type);

        $invalidType = TypeValidatorFactory::createFromTypeName('invalid');
        $this->assertNull($invalidType);
    }

    public function testMakeTypeCompatible(): void
    {
        $intType = new Type('integer');
        $result = TypeValidatorFactory::makeTypeCompatible($intType);

        $this->assertInstanceOf(AtLeastOneOf::class, $result);
    }

    public function testCreateFromUnionType(): void
    {
        $reflection = new \ReflectionClass(new class {
            public string|int $unionProperty;
        });

        $property = $reflection->getProperty('unionProperty');
        $type = $property->getType();
        $this->assertNotNull($type);

        $result = TypeValidatorFactory::createFromReflectionType($type);

        $this->assertInstanceOf(AtLeastOneOf::class, $result);
    }
}
