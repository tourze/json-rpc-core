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
use Tourze\JsonRPC\Core\Helper\ParamObjectFactory;
use Tourze\JsonRPC\Core\Tests\Fixtures\AssociativeArrayParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\DefaultArrayParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\IntArrayParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\MixedArrayParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\OptionalArrayParam;
use Tourze\JsonRPC\Core\Tests\Fixtures\StringArrayParam;

/**
 * ParamObjectFactory 数组类型反序列化测试
 *
 * @internal
 */
#[CoversClass(ParamObjectFactory::class)]
final class ParamObjectFactoryArrayTest extends TestCase
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

    public function testCreateWithStringArray(): void
    {
        $data = [
            'title' => 'Tags List',
            'tags' => ['php', 'symfony', 'json-rpc'],
        ];

        /** @var StringArrayParam $param */
        $param = $this->factory->create(StringArrayParam::class, $data);

        $this->assertSame('Tags List', $param->title);
        $this->assertIsArray($param->tags);
        $this->assertCount(3, $param->tags);
        $this->assertSame(['php', 'symfony', 'json-rpc'], $param->tags);
    }

    public function testCreateWithIntArray(): void
    {
        $data = [
            'name' => 'Numbers',
            'values' => [1, 2, 3, 4, 5],
        ];

        /** @var IntArrayParam $param */
        $param = $this->factory->create(IntArrayParam::class, $data);

        $this->assertSame('Numbers', $param->name);
        $this->assertIsArray($param->values);
        $this->assertCount(5, $param->values);
        $this->assertSame([1, 2, 3, 4, 5], $param->values);
    }

    public function testCreateWithEmptyArray(): void
    {
        $data = [
            'title' => 'Empty Tags',
            'tags' => [],
        ];

        /** @var StringArrayParam $param */
        $param = $this->factory->create(StringArrayParam::class, $data);

        $this->assertSame('Empty Tags', $param->title);
        $this->assertIsArray($param->tags);
        $this->assertCount(0, $param->tags);
    }

    public function testCreateWithMixedArray(): void
    {
        $data = [
            'name' => 'Mixed Data',
            'items' => ['text', 123, true, 45.6],
        ];

        /** @var MixedArrayParam $param */
        $param = $this->factory->create(MixedArrayParam::class, $data);

        $this->assertSame('Mixed Data', $param->name);
        $this->assertIsArray($param->items);
        $this->assertCount(4, $param->items);
    }

    public function testCreateWithOptionalArrayNull(): void
    {
        $data = [
            'name' => 'No Items',
        ];

        /** @var OptionalArrayParam $param */
        $param = $this->factory->create(OptionalArrayParam::class, $data);

        $this->assertSame('No Items', $param->name);
        $this->assertNull($param->items);
    }

    public function testCreateWithOptionalArrayProvided(): void
    {
        $data = [
            'name' => 'With Items',
            'items' => ['a', 'b', 'c'],
        ];

        /** @var OptionalArrayParam $param */
        $param = $this->factory->create(OptionalArrayParam::class, $data);

        $this->assertSame('With Items', $param->name);
        $this->assertSame(['a', 'b', 'c'], $param->items);
    }

    public function testCreateWithDefaultEmptyArray(): void
    {
        $data = [
            'name' => 'Default Array',
        ];

        /** @var DefaultArrayParam $param */
        $param = $this->factory->create(DefaultArrayParam::class, $data);

        $this->assertSame('Default Array', $param->name);
        $this->assertIsArray($param->items);
        $this->assertCount(0, $param->items);
    }

    public function testCreateWithAssociativeArray(): void
    {
        $data = [
            'name' => 'Metadata',
            'metadata' => [
                'key1' => 'value1',
                'key2' => 'value2',
                'nested' => ['a' => 1, 'b' => 2],
            ],
        ];

        /** @var AssociativeArrayParam $param */
        $param = $this->factory->create(AssociativeArrayParam::class, $data);

        $this->assertSame('Metadata', $param->name);
        $this->assertIsArray($param->metadata);
        $this->assertSame('value1', $param->metadata['key1']);
        $this->assertSame('value2', $param->metadata['key2']);
        $this->assertIsArray($param->metadata['nested']);
    }

    public function testSupportsReturnsTrueForRpcParamImplementation(): void
    {
        $this->assertTrue($this->factory->supports(StringArrayParam::class));
        $this->assertTrue($this->factory->supports(IntArrayParam::class));
        $this->assertTrue($this->factory->supports(MixedArrayParam::class));
    }

    public function testSupportsReturnsFalseForNonRpcParamClass(): void
    {
        $this->assertFalse($this->factory->supports(\stdClass::class));
        $this->assertFalse($this->factory->supports(\DateTime::class));
    }
}
