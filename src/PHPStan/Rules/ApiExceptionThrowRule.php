<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\Throw_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;

/**
 * 检查抛出 ApiException 或其子类时，所在的类必须实现 JsonRpcMethodInterface.
 *
 * @implements Rule<Throw_>
 */
class ApiExceptionThrowRule implements Rule
{
    public function getNodeType(): string
    {
        return Throw_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $thrownType = $scope->getType($node->expr);
        $apiExceptionType = new ObjectType(ApiException::class);

        // 检查抛出的异常是否为 ApiException 或其子类
        if (!$apiExceptionType->isSuperTypeOf($thrownType)->yes()) {
            return [];
        }

        // 获取当前类
        $classReflection = $scope->getClassReflection();
        if (null === $classReflection) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'ApiException or its subclass can only be thrown within a class that implements %s.',
                    JsonRpcMethodInterface::class
                ))
                    ->line($node->getStartLine())
                    ->build(),
            ];
        }

        // 对于测试数据文件，不跳过检查；对于 json-rpc 包的源文件跳过检查
        $fileName = $classReflection->getFileName();
        if (str_contains($fileName, 'json-rpc-') && !str_contains($fileName, '/tests/') && !str_contains($fileName, '/data/')) {
            return [];
        }

        $jsonRpcMethodInterfaceType = new ObjectType(JsonRpcMethodInterface::class);

        // 检查当前类是否实现了 JsonRpcMethodInterface
        if (!$jsonRpcMethodInterfaceType->isSuperTypeOf(new ObjectType($classReflection->getName()))->yes()) {
            return [
                RuleErrorBuilder::message(sprintf(
                    'Class %s throws ApiException or its subclass but does not implement %s.',
                    $classReflection->getName(),
                    JsonRpcMethodInterface::class
                ))
                    ->line($node->getStartLine())
                    ->build(),
            ];
        }

        return [];
    }
}
