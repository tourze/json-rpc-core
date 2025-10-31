<?php

namespace Tourze\JsonRPC\Core\Tests\Helper;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Helper\PropertyConstraintExtractor;

/**
 * @internal
 */
#[CoversClass(PropertyConstraintExtractor::class)]
final class PropertyConstraintExtractorTest extends TestCase
{
    public function testExtractConstraintFromSimpleProperty(): void
    {
        $reflection = new \ReflectionClass(new class {
            public string $name;
        });

        $property = $reflection->getProperty('name');
        $constraint = PropertyConstraintExtractor::extractConstraint($property);

        $this->assertNotNull($constraint);
    }

    public function testExtractConstraintFromPropertyWithoutType(): void
    {
        $reflection = new \ReflectionClass(new class {
            public mixed $noType;
        });

        $property = $reflection->getProperty('noType');
        $constraint = PropertyConstraintExtractor::extractConstraint($property);

        $this->assertNull($constraint);
    }
}
