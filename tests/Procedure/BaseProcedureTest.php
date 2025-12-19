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
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithResultDocInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithValidatedParamsInterface;
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPC\Core\Result\SuccessResult;
use Tourze\JsonRPC\Core\Tests\Fixtures\TestProcedureParam;
use Tourze\PHPUnitJsonRPC\AbstractProcedureTestCase;

/**
 * 测试 BaseProcedure 抽象类 - 参数对象模式
 *
 * @internal
 */
#[CoversClass(BaseProcedure::class)]
#[RunTestsInSeparateProcesses]
final class BaseProcedureTest extends AbstractProcedureTestCase
{
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
            public function execute(RpcParamInterface $param): RpcResultInterface
            {
                // 参数对象由框架注入，这里直接访问具体类的属性
                /** @var TestProcedureParam $testParam */
                $testParam = $param;

                $encoded = json_encode([
                    'name' => $testParam->name,
                    'age' => $testParam->age,
                    'processed' => true,
                ]);

                return new SuccessResult(
                    success: true,
                    message: false !== $encoded ? $encoded : null
                );
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

    public function testExecuteMethodWithParamObject(): void
    {
        $procedure = $this->createTestProcedure();

        // 创建一个测试参数对象
        $param = new TestProcedureParam('John Doe', 30);
        $result = $procedure->execute($param);

        $this->assertInstanceOf(RpcResultInterface::class, $result);
        $this->assertInstanceOf(SuccessResult::class, $result);
        $this->assertTrue($result->success);
    }

    public function testBaseProcedureComplexity(): void
    {
        // 检查 BaseProcedure 类的复杂性指标
        $reflectionClass = new \ReflectionClass(BaseProcedure::class);

        // 检查方法数量 - BaseProcedure 应该保持精简
        $methodCount = count($reflectionClass->getMethods());
        $this->assertGreaterThan(8, $methodCount,
            'BaseProcedure 类方法过多（当前 ' . $methodCount . ' 个方法），建议拆分职责。'
        );

        // 检查类文件行数
        $fileName = $reflectionClass->getFileName();
        if (is_string($fileName)) {
            $content = file_get_contents($fileName);
            if (is_string($content)) {
                $lineCount = substr_count($content, "\n") + 1;
                $this->assertLessThan(400, $lineCount,
                    'BaseProcedure 类过于复杂（' . $lineCount . '行），建议重构。'
                );
            }
        }
    }
}
