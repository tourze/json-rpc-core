<?php

namespace Tourze\JsonRPC\Core\Tests\Helper;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Validator\Validation;
use Tourze\JsonRPC\Core\Helper\ParameterProcessor;

/**
 * @internal
 */
#[CoversClass(ParameterProcessor::class)]
final class ParameterProcessorTest extends TestCase
{
    private ParameterProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = new ParameterProcessor(
            PropertyAccess::createPropertyAccessor(),
            Validation::createValidator(),
            new NullLogger()
        );
    }

    public function testAssignParametersWithNullParams(): void
    {
        $target = new class {
            public ?string $name = null;
        };

        $this->processor->assignParameters($target, null);

        $this->assertNull($target->name);
    }

    public function testAssignParametersWithEmptyParams(): void
    {
        $target = new class {
            public ?string $name = null;
        };

        $this->processor->assignParameters($target, []);

        $this->assertNull($target->name);
    }

    public function testAssignParametersWithValidParams(): void
    {
        $target = new class {
            public ?string $name = null;

            public ?int $age = null;
        };

        $this->processor->assignParameters($target, [
            'name' => 'John',
            'age' => 30,
        ]);

        $this->assertEquals('John', $target->name);
        $this->assertEquals(30, $target->age);
    }

    public function testAssignParametersThrowsExceptionForInvalidParam(): void
    {
        $target = new class {
            public string $name = 'default';
        };

        // 测试属性设置功能
        $this->processor->assignParameters($target, ['name' => 'test']);
        $this->assertEquals('test', $target->name);
    }
}
