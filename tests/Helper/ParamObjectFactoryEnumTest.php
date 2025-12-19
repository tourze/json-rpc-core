<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Helper;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validation;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Helper\ParamObjectFactory;
use Tourze\JsonRPC\Core\Tests\Fixtures\DefaultEnumParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\IntEnumParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\MultiEnumParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\OptionalEnumParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\PriorityEnum;
use Tourze\JsonRPC\Core\Tests\Fixtures\StringEnumParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\UserStatusEnum;

/**
 * ParamObjectFactory 枚举类型反序列化测试
 *
 * @internal
 */
#[CoversClass(ParamObjectFactory::class)]
final class ParamObjectFactoryEnumTest extends TestCase
{
    private ParamObjectFactory $factory;

    protected function setUp(): void
    {
        $serializer = new Serializer(
            [new BackedEnumNormalizer(), new ArrayDenormalizer(), new ObjectNormalizer()],
            [new JsonEncoder()]
        );
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $this->factory = new ParamObjectFactory($serializer, $validator);
    }

    public function testCreateWithStringBackedEnum(): void
    {
        $data = [
            'name' => 'Test User',
            'status' => 'active',
        ];

        /** @var StringEnumParam $param */
        $param = $this->factory->create(StringEnumParam::class, $data);

        $this->assertSame('Test User', $param->name);
        $this->assertSame(UserStatusEnum::ACTIVE, $param->status);
        $this->assertSame('active', $param->status->value);
    }

    public function testCreateWithIntBackedEnum(): void
    {
        $data = [
            'name' => 'High Priority Task',
            'priority' => 1,
        ];

        /** @var IntEnumParam $param */
        $param = $this->factory->create(IntEnumParam::class, $data);

        $this->assertSame('High Priority Task', $param->name);
        $this->assertSame(PriorityEnum::HIGH, $param->priority);
        $this->assertSame(1, $param->priority->value);
    }

    public function testCreateWithAllStringEnumValues(): void
    {
        $testCases = [
            ['status' => 'active', 'expected' => UserStatusEnum::ACTIVE],
            ['status' => 'inactive', 'expected' => UserStatusEnum::INACTIVE],
            ['status' => 'pending', 'expected' => UserStatusEnum::PENDING],
        ];

        foreach ($testCases as $case) {
            $data = ['name' => 'User', 'status' => $case['status']];
            /** @var StringEnumParam $param */
            $param = $this->factory->create(StringEnumParam::class, $data);
            $this->assertSame($case['expected'], $param->status);
        }
    }

    public function testCreateWithAllIntEnumValues(): void
    {
        $testCases = [
            ['priority' => 1, 'expected' => PriorityEnum::HIGH],
            ['priority' => 2, 'expected' => PriorityEnum::MEDIUM],
            ['priority' => 3, 'expected' => PriorityEnum::LOW],
        ];

        foreach ($testCases as $case) {
            $data = ['name' => 'Task', 'priority' => $case['priority']];
            /** @var IntEnumParam $param */
            $param = $this->factory->create(IntEnumParam::class, $data);
            $this->assertSame($case['expected'], $param->priority);
        }
    }

    public function testCreateWithInvalidStringEnumValueThrowsException(): void
    {
        $data = [
            'name' => 'Invalid User',
            'status' => 'unknown_status',  // Invalid enum value
        ];

        $this->expectException(ApiException::class);

        $this->factory->create(StringEnumParam::class, $data);
    }

    public function testCreateWithInvalidIntEnumValueThrowsException(): void
    {
        $data = [
            'name' => 'Invalid Task',
            'priority' => 999,  // Invalid enum value
        ];

        $this->expectException(ApiException::class);

        $this->factory->create(IntEnumParam::class, $data);
    }

    public function testCreateWithOptionalEnumNull(): void
    {
        $data = [
            'name' => 'No Status User',
        ];

        /** @var OptionalEnumParam $param */
        $param = $this->factory->create(OptionalEnumParam::class, $data);

        $this->assertSame('No Status User', $param->name);
        $this->assertNull($param->status);
    }

    public function testCreateWithOptionalEnumProvided(): void
    {
        $data = [
            'name' => 'Active User',
            'status' => 'active',
        ];

        /** @var OptionalEnumParam $param */
        $param = $this->factory->create(OptionalEnumParam::class, $data);

        $this->assertSame('Active User', $param->name);
        $this->assertSame(UserStatusEnum::ACTIVE, $param->status);
    }

    public function testCreateWithDefaultEnumValue(): void
    {
        $data = [
            'name' => 'Default Priority Task',
        ];

        /** @var DefaultEnumParam $param */
        $param = $this->factory->create(DefaultEnumParam::class, $data);

        $this->assertSame('Default Priority Task', $param->name);
        $this->assertSame(PriorityEnum::MEDIUM, $param->priority);
    }

    public function testCreateWithMultipleEnums(): void
    {
        $data = [
            'name' => 'Complex Item',
            'status' => 'pending',
            'priority' => 1,
        ];

        /** @var MultiEnumParam $param */
        $param = $this->factory->create(MultiEnumParam::class, $data);

        $this->assertSame('Complex Item', $param->name);
        $this->assertSame(UserStatusEnum::PENDING, $param->status);
        $this->assertSame(PriorityEnum::HIGH, $param->priority);
    }

    public function testSupportsReturnsTrueForRpcParamImplementation(): void
    {
        $this->assertTrue($this->factory->supports(StringEnumParam::class));
        $this->assertTrue($this->factory->supports(IntEnumParam::class));
        $this->assertTrue($this->factory->supports(OptionalEnumParam::class));
    }

    public function testSupportsReturnsFalseForNonRpcParamClass(): void
    {
        $this->assertFalse($this->factory->supports(\stdClass::class));
        $this->assertFalse($this->factory->supports(\DateTime::class));
    }
}
