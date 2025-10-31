<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Serialization;

use Tourze\JsonRPC\Core\Exception\JsonRpcInvalidRequestException;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * Class JsonRpcRequestDenormalizer.
 */
class JsonRpcRequestDenormalizer
{
    final public const KEY_JSON_RPC = 'jsonrpc';

    final public const KEY_ID = 'id';

    final public const KEY_METHOD = 'method';

    final public const KEY_PARAM_LIST = 'params';

    /**
     * @param mixed $item Should be an array
     *
     * @throws JsonRpcInvalidRequestException
     */
    public function denormalize(mixed $item): JsonRpcRequest
    {
        $this->validateArray($item, 'Item must be an array');
        assert(is_array($item));

        // Validate json-rpc and method keys
        $this->validateRequiredKey($item, self::KEY_JSON_RPC);
        $this->validateRequiredKey($item, self::KEY_METHOD);

        $request = new JsonRpcRequest();

        $jsonrpc = $item[self::KEY_JSON_RPC];
        assert(is_string($jsonrpc));
        $request->setJsonrpc($jsonrpc);

        $method = $item[self::KEY_METHOD];
        assert(is_string($method));
        $request->setMethod($method);

        $this->bindIdIfProvided($request, $item);
        $this->bindParamListIfProvided($request, $item);

        return $request;
    }

    /**
     * @param array<mixed> $item
     */
    protected function bindIdIfProvided(JsonRpcRequest $request, array $item): void
    {
        /* If no id defined => request is a notification */
        if (isset($item[self::KEY_ID])) {
            $idValue = $item[self::KEY_ID];
            // Check if the value is an integer or a string containing an integer
            if (is_int($idValue)) {
                $request->setId($idValue);
            } elseif (is_string($idValue)) {
                // Check if string contains an int
                if ((string) ((int) $idValue) === $idValue) {
                    // Convert string containing an int to int
                    $request->setId((int) $idValue);
                } else {
                    // Keep as string
                    $request->setId($idValue);
                }
            } else {
                // For other types, let setId handle the validation/error
                $request->setId($idValue);
            }
        }
    }

    /**
     * @param array<mixed> $item
     *
     * @throws JsonRpcInvalidRequestException
     */
    protected function bindParamListIfProvided(JsonRpcRequest $request, array $item): void
    {
        $params = new JsonRpcParams();
        if (isset($item[self::KEY_PARAM_LIST])) {
            $paramList = $item[self::KEY_PARAM_LIST];
            $this->validateArray($paramList, 'Parameter list must be an array');
            assert(is_array($paramList));
            $params->replace($paramList);
        }
        $request->setParams($params);
    }

    /**
     * @throws JsonRpcInvalidRequestException
     */
    private function validateArray(mixed $value, string $errorDescription): void
    {
        if (!is_array($value)) {
            throw new JsonRpcInvalidRequestException($value, $errorDescription);
        }
    }

    /**
     * @param array<mixed> $item
     *
     * @throws JsonRpcInvalidRequestException
     */
    private function validateRequiredKey(array $item, string $key): void
    {
        if (!isset($item[$key])) {
            throw new JsonRpcInvalidRequestException($item, sprintf('"%s" is a required key', $key));
        }
    }
}
