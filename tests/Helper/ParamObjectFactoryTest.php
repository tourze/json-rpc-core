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
use Tourze\JsonRPC\Core\Tests\Fixtures\NullableTestParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\SimpleTestParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\ValidatedTestParam;

/**
 * ParamObjectFactory 单元测试
 */
#[CoversClass(ParamObjectFactory::class)]
class ParamObjectFactoryTest extends TestCase
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

    public function testSupportsReturnsTrueForRpcParamInterface(): void
    {
        $paramClass = $this->createSimpleParamClass();

        $this->assertTrue($this->factory->supports($paramClass));
    }

    public function testSupportsReturnsFalseForNonRpcParamInterface(): void
    {
        $this->assertFalse($this->factory->supports(\stdClass::class));
    }

    public function testCreateWithBasicTypes(): void
    {
        $paramClass = $this->createSimpleParamClass();
        $data = ['name' => 'John', 'age' => 25, 'active' => true, 'score' => 95.5];

        /** @var SimpleTestParam $param */
        $param = $this->factory->create($paramClass, $data);

        $this->assertInstanceOf(RpcParamInterface::class, $param);
        $this->assertSame('John', $param->name);
        $this->assertSame(25, $param->age);
        $this->assertTrue($param->active);
        $this->assertSame(95.5, $param->score);
    }

    public function testCreateWithDefaultValues(): void
    {
        $paramClass = $this->createSimpleParamClass();
        $data = ['name' => 'Jane'];

        /** @var SimpleTestParam $param */
        $param = $this->factory->create($paramClass, $data);

        $this->assertSame('Jane', $param->name);
        $this->assertSame(0, $param->age);
        $this->assertTrue($param->active);
        $this->assertSame(0.0, $param->score);
    }

    public function testCreateThrowsExceptionForInvalidClass(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessageMatches('/必须实现.*RpcParamInterface/');

        $this->factory->create(\stdClass::class, ['foo' => 'bar']);
    }

    public function testCreateThrowsExceptionForValidationFailure(): void
    {
        $paramClass = $this->createValidatedParamClass();
        $data = ['email' => 'invalid-email'];

        $this->expectException(ApiException::class);
        $this->expectExceptionMessageMatches('/校验不通过/');

        $this->factory->create($paramClass, $data);
    }

    public function testCreateWithOptionalNullableField(): void
    {
        $paramClass = $this->createNullableParamClass();
        $data = ['required' => 'value'];

        /** @var NullableTestParam $param */
        $param = $this->factory->create($paramClass, $data);

        $this->assertSame('value', $param->required);
        $this->assertNull($param->optional);
    }

    /**
     * 创建简单参数类
     *
     * @return class-string<RpcParamInterface>
     */
    private function createSimpleParamClass(): string
    {
        // 使用匿名类定义会在每次调用时创建新类，这里使用预定义的测试类
        return SimpleTestParam::class;
    }

    /**
     * 创建带验证的参数类
     *
     * @return class-string<RpcParamInterface>
     */
    private function createValidatedParamClass(): string
    {
        return ValidatedTestParam::class;
    }

    /**
     * 创建可空参数类
     *
     * @return class-string<RpcParamInterface>
     */
    private function createNullableParamClass(): string
    {
        return NullableTestParam::class;
    }
}
