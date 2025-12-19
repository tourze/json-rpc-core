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
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Helper\ParamObjectFactory;
use Tourze\JsonRPC\Core\Tests\Fixtures\AddressParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\ContactParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\CustomerParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\NestedOptionalParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\NestedThreeLevelParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\NestedTwoLevelParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\NestedWithValidationParam;

/**
 * ParamObjectFactory 嵌套对象反序列化测试
 *
 * @internal
 */
#[CoversClass(ParamObjectFactory::class)]
final class ParamObjectFactoryNestedTest extends TestCase
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

    public function testCreateWithTwoLevelNestedObject(): void
    {
        $data = [
            'name' => 'Order-001',
            'address' => [
                'street' => '123 Main St',
                'city' => 'Shanghai',
            ],
        ];

        /** @var NestedTwoLevelParam $param */
        $param = $this->factory->create(NestedTwoLevelParam::class, $data);

        $this->assertSame('Order-001', $param->name);
        $this->assertInstanceOf(AddressParam::class, $param->address);
        $this->assertSame('123 Main St', $param->address->street);
        $this->assertSame('Shanghai', $param->address->city);
    }

    public function testCreateWithThreeLevelNestedObject(): void
    {
        $data = [
            'orderId' => 'ORD-123',
            'customer' => [
                'name' => 'John Doe',
                'contact' => [
                    'phone' => '13800138000',
                    'email' => 'john@example.com',
                ],
            ],
        ];

        /** @var NestedThreeLevelParam $param */
        $param = $this->factory->create(NestedThreeLevelParam::class, $data);

        $this->assertSame('ORD-123', $param->orderId);
        $this->assertInstanceOf(CustomerParam::class, $param->customer);
        $this->assertSame('John Doe', $param->customer->name);
        $this->assertInstanceOf(ContactParam::class, $param->customer->contact);
        $this->assertSame('13800138000', $param->customer->contact->phone);
        $this->assertSame('john@example.com', $param->customer->contact->email);
    }

    public function testCreateWithOptionalNestedObjectNull(): void
    {
        $data = [
            'name' => 'Order-002',
        ];

        /** @var NestedOptionalParam $param */
        $param = $this->factory->create(NestedOptionalParam::class, $data);

        $this->assertSame('Order-002', $param->name);
        $this->assertNull($param->address);
    }

    public function testCreateWithOptionalNestedObjectProvided(): void
    {
        $data = [
            'name' => 'Order-003',
            'address' => [
                'street' => '456 Oak Ave',
                'city' => 'Beijing',
            ],
        ];

        /** @var NestedOptionalParam $param */
        $param = $this->factory->create(NestedOptionalParam::class, $data);

        $this->assertSame('Order-003', $param->name);
        $this->assertNotNull($param->address);
        $this->assertSame('456 Oak Ave', $param->address->street);
        $this->assertSame('Beijing', $param->address->city);
    }

    public function testNestedObjectValidationWithValidAnnotation(): void
    {
        $data = [
            'title' => 'My Order',
            'contact' => [
                'phone' => '',  // Invalid: NotBlank constraint
                'email' => 'test@example.com',
            ],
        ];

        $this->expectException(ApiException::class);
        $this->expectExceptionMessageMatches('/校验不通过/');

        $this->factory->create(NestedWithValidationParam::class, $data);
    }

    public function testNestedObjectValidationPasses(): void
    {
        $data = [
            'title' => 'Valid Order',
            'contact' => [
                'phone' => '13800138000',
                'email' => 'valid@example.com',
            ],
        ];

        /** @var NestedWithValidationParam $param */
        $param = $this->factory->create(NestedWithValidationParam::class, $data);

        $this->assertSame('Valid Order', $param->title);
        $this->assertSame('13800138000', $param->contact->phone);
    }

    public function testSupportsReturnsTrueForRpcParamImplementation(): void
    {
        $this->assertTrue($this->factory->supports(NestedTwoLevelParam::class));
        $this->assertTrue($this->factory->supports(NestedThreeLevelParam::class));
        $this->assertTrue($this->factory->supports(NestedOptionalParam::class));
    }

    public function testSupportsReturnsFalseForNonRpcParamClass(): void
    {
        $this->assertFalse($this->factory->supports(\stdClass::class));
        $this->assertFalse($this->factory->supports(\DateTime::class));
    }
}
