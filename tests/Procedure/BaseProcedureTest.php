<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Procedure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithResultDocInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithValidatedParamsInterface;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * 测试BaseProcedure抽象类.
 *
 * TODO: 这个测试其实不应该用集成测试，后面要改造
 *
 * @internal
 */
#[CoversClass(BaseProcedure::class)]
#[RunTestsInSeparateProcesses]
final class BaseProcedureTest extends AbstractProcedureTestCase
{
    /**
     * 指定被测的 Procedure 类.
     */
    protected const PROCEDURE_CLASS = BaseProcedure::class;

    protected function onSetUp(): void
    {
        // 无需额外的初始化逻辑
    }

    private function createTestProcedure(): BaseProcedure
    {
        return new #[MethodTag(name: 'test')]
        #[MethodDoc(summary: 'Test Procedure', description: 'A test procedure for unit testing')]
        #[MethodExpose(method: 'testProcedure')]
        class extends BaseProcedure {
            public string $name = '';

            public int $age = 0;

            public function execute(): array
            {
                return [
                    'name' => $this->name,
                    'age' => $this->age,
                    'processed' => true,
                ];
            }

            public function getParamsConstraint(): Collection
            {
                return new Collection(fields: [], allowExtraFields: true, allowMissingFields: true);
            }
        };
    }

    public function testBaseProcedureImplementsRequiredInterfaces(): void
    {
        $procedure = $this->createTestProcedure();

        $this->assertInstanceOf(JsonRpcMethodInterface::class, $procedure);
        $this->assertInstanceOf(MethodWithValidatedParamsInterface::class, $procedure);
        $this->assertInstanceOf(MethodWithResultDocInterface::class, $procedure);
        $this->assertInstanceOf(ServiceSubscriberInterface::class, $procedure);
    }

    public function testGetParamsConstraint(): void
    {
        $procedure = $this->createTestProcedure();
        $constraints = $procedure->getParamsConstraint();

        $this->assertInstanceOf(Collection::class, $constraints);
    }

    public function testExecuteMethod(): void
    {
        $procedure = $this->createTestProcedure();

        // 使用反射设置属性进行测试
        $reflection = new \ReflectionClass($procedure);
        $nameProperty = $reflection->getProperty('name');
        $nameProperty->setValue($procedure, 'John Doe');

        $ageProperty = $reflection->getProperty('age');
        $ageProperty->setValue($procedure, 30);

        $result = $procedure->execute();

        $this->assertEquals([
            'name' => 'John Doe',
            'age' => 30,
            'processed' => true,
        ], $result);
    }

    public function testGetMockResult(): void
    {
        $result = BaseProcedure::getMockResult();
        $this->assertNull($result);
    }

    public function testBaseProcedureComplexity(): void
    {
        // 检查 BaseProcedure 类的复杂性指标
        $reflectionClass = new \ReflectionClass(BaseProcedure::class);

        // 检查方法数量 - BaseProcedure 应该保持精简
        $methodCount = count($reflectionClass->getMethods());
        $this->assertGreaterThan(8, $methodCount,
            'BaseProcedure类方法过多（当前 ' . $methodCount . ' 个方法），建议拆分职责。'
        );

        // 检查类文件行数
        $fileName = $reflectionClass->getFileName();
        if (is_string($fileName)) {
            $content = file_get_contents($fileName);
            if (is_string($content)) {
                $lineCount = substr_count($content, "\n") + 1;
                $this->assertLessThan(400, $lineCount,
                    'BaseProcedure类过于复杂（' . $lineCount . '行），建议重构。'
                );
            }
        }
    }
}
