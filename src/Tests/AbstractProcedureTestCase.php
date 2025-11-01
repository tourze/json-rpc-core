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
 * - 子类必须使用 #[CoversClass] 注解指定被测的 Procedure 类
 * - 此类被 NoAbstractIntegrationTestCaseRule 白名单允许，因为它遵循 *TestCase 命名约定
 */
#[CoversClass(AbstractIntegrationTestCase::class)]
#[RunTestsInSeparateProcesses]
abstract class AbstractProcedureTestCase extends AbstractIntegrationTestCase
{

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
        $reflectionClass = new \ReflectionClass($this);
        $coverClass = TestCaseHelper::extractCoverClass($reflectionClass);

        if (null === $coverClass) {
            $this->markTestSkipped(
                '未找到 #[CoversClass] 注解。请在测试类上添加 #[CoversClass(YourProcedure::class)] 注解。'
            );
        }

        if (!class_exists($coverClass)) {
            $this->markTestSkipped(
                sprintf('CoversClass 指定的类 "%s" 不存在', $coverClass)
            );
        }

        $reflection = new \ReflectionClass($coverClass);
        $attributes = $reflection->getAttributes();

        $attributeNames = array_map(fn ($attr) => $attr->getName(), $attributes);

        $this->assertContains(MethodTag::class, $attributeNames,
            sprintf('Procedure 类 "%s" 缺少 #[MethodTag] 注解', $coverClass)
        );
        $this->assertContains(MethodDoc::class, $attributeNames,
            sprintf('Procedure 类 "%s" 缺少 #[MethodDoc] 注解', $coverClass)
        );
        $this->assertContains(MethodExpose::class, $attributeNames,
            sprintf('Procedure 类 "%s" 缺少 #[MethodExpose] 注解', $coverClass)
        );
    }
}
