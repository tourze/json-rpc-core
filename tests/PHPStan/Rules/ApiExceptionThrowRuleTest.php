<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\PHPStan\Rules;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Tourze\JsonRPC\Core\PHPStan\Rules\ApiExceptionThrowRule;

/**
 * @extends RuleTestCase<ApiExceptionThrowRule>
 *
 * @internal
 */
#[CoversNothing]
class ApiExceptionThrowRuleTest extends RuleTestCase
{
    public function testValidApiExceptionThrowInJsonRpcMethod(): void
    {
        $this->analyse([__DIR__ . '/Data/ValidApiExceptionThrow.php'], []);
    }

    public function testInvalidApiExceptionThrowInNonJsonRpcMethod(): void
    {
        $this->analyse([__DIR__ . '/Data/InvalidApiExceptionThrow.php'], [
            [
                'Class Tourze\JsonRPC\Core\Tests\PHPStan\Rules\Data\InvalidApiExceptionThrow throws ApiException or its subclass but does not implement Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface.',
                14,
            ],
        ]);
    }

    public function testApiExceptionThrowOutsideClass(): void
    {
        $this->analyse([__DIR__ . '/Data/ApiExceptionThrowOutsideClass.php'], [
            [
                'ApiException or its subclass can only be thrown within a class that implements Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface.',
                7,
            ],
        ]);
    }

    public function testNonApiExceptionThrow(): void
    {
        $this->analyse([__DIR__ . '/Data/NonApiExceptionThrow.php'], []);
    }

    protected function getRule(): Rule
    {
        return new ApiExceptionThrowRule();
    }
}
