<?php

namespace Tourze\JsonRPC\Core\Domain;

use Symfony\Component\Validator\Constraints\Collection;

/**
 * Interface MethodWithValidatedParamsInterface
 */
interface MethodWithValidatedParamsInterface
{
    /**
     * @return Collection Usually a Collection constraint
     */
    public function getParamsConstraint(): Collection;
}
