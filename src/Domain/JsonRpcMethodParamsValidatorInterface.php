<?php

namespace Tourze\JsonRPC\Core\Domain;


use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * Interface JsonRpcMethodParamsValidatorInterface
 */
interface JsonRpcMethodParamsValidatorInterface
{
    /**
     * @return array An array of violations
     */
    public function validate(JsonRpcRequest $jsonRpcRequest, JsonRpcMethodInterface $method): array;
}
