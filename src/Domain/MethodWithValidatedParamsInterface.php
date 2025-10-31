<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Domain;

use Symfony\Component\Validator\Constraints\Collection;

/**
 * 带有验证参数的方法接口.
 */
interface MethodWithValidatedParamsInterface
{
    /**
     * @return Collection Usually a Collection constraint
     */
    public function getParamsConstraint(): Collection;
}
