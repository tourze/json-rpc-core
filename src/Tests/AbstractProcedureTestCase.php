<?php

namespace Tourze\JsonRPC\Core\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\PHPUnitBase\TestCaseHelper;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * JsonRPC Procedure 测试抽象基类.
 *
 * 设计说明：
 * - 提供 Procedure 测试的通用断言方法
 * - 通过 PROCEDURE_CLASS 常量模式控制测试行为
 * - 子类必须覆盖 PROCEDURE_CLASS 常量指定被测类
 * - 此类被 NoAbstractIntegrationTestCaseRule 白名单允许，因为它遵循 *TestCase 命名约定
 */
#[CoversClass(AbstractIntegrationTestCase::class)]
#[RunTestsInSeparateProcesses]
abstract class AbstractProcedureTestCase extends AbstractIntegrationTestCase
{
    /**
     * 子类必须覆盖此常量，指定被测的 Procedure 类.
     *
     * @var class-string
     */
    protected const PROCEDURE_CLASS = '';

    /**
     * {@inheritDoc}
     */
    protected function onSetUp(): void
    {
        // 默认无需额外的初始化逻辑
        // 子类可以覆盖此方法添加特定的初始化
    }

    /**
     * 验证 Procedure 类包含必需的注解.
     *
     * 所有 Procedure 类都必须包含：
     * - MethodTag: 方法标签
     * - MethodDoc: 方法文档
     * - MethodExpose: 方法暴露配置
     */
    final public function testProcedureHasRequiredAttributes(): void
    {
        // 如果子类未覆盖常量，跳过测试
        if ('' === static::PROCEDURE_CLASS) {
            $this->markTestSkipped(
                'PROCEDURE_CLASS 常量未设置。请在子类中覆盖此常量指定被测的 Procedure 类。'
            );
        }

        $reflectionClass = new \ReflectionClass($this);
        $coverClass = TestCaseHelper::extractCoverClass($reflectionClass);
        if (null === $coverClass) {
            return;
        }

        if (!class_exists($coverClass)) {
            return;
        }

        $reflection = new \ReflectionClass($coverClass);
        $attributes = $reflection->getAttributes();

        $attributeNames = array_map(fn ($attr) => $attr->getName(), $attributes);

        $this->assertContains(MethodTag::class, $attributeNames);
        $this->assertContains(MethodDoc::class, $attributeNames);
        $this->assertContains(MethodExpose::class, $attributeNames);
    }
}
