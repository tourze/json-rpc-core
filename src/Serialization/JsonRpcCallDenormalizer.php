<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Serialization;

use Tourze\JsonRPC\Core\Model\JsonRpcCallRequest;

/**
 * Class JsonRpcCallDenormalizer.
 */
readonly class JsonRpcCallDenormalizer
{
    public function __construct(private JsonRpcRequestDenormalizer $requestDenormalizer)
    {
    }

    /**
     * @param array<mixed> $decodedContent
     */
    public function denormalize(array $decodedContent): JsonRpcCallRequest
    {
        $jsonRpcCall = new JsonRpcCallRequest(
            self::guessBatchOrNot($decodedContent)
        );

        $this->populateItem($jsonRpcCall, $decodedContent);

        return $jsonRpcCall;
    }

    /**
     * @param array<mixed> $decodedContent
     */
    private static function guessBatchOrNot(array $decodedContent): bool
    {
        $isBatch = ([] !== $decodedContent);
        // Loop over each items
        // -> In case it's a valid batch request -> all keys will have numeric type -> iterations = Number of requests
        // -> In case it's a valid normal request -> all keys will have string type -> iterations = 1 (stopped on #1)
        // => Better performance for normal request (Probably most of requests)
        foreach ($decodedContent as $key => $item) {
            // At least a key is a string (that do not contain an int)
            if (!is_int($key)) {
                $isBatch = false;
                break;
            }
        }

        return $isBatch;
    }

    /**
     * @param array<mixed> $decodedContent
     */
    private function populateItem(JsonRpcCallRequest $jsonRpcCall, array $decodedContent): void
    {
        // convert to array in any cases for simpler use
        $itemList = $jsonRpcCall->isBatch() ? $decodedContent : [$decodedContent];
        foreach ($itemList as $item) {
            // Safely denormalize items
            try {
                $item = $this->requestDenormalizer->denormalize($item);

                $jsonRpcCall->addRequestItem($item);
            } catch (\Throwable $exception) {
                if (false === $jsonRpcCall->isBatch()) {
                    // If it's not a batch call, throw the exception
                    throw $exception;
                }

                // Else populate the item (exception will be managed later
                // Convert Throwable to Exception if needed
                if (!$exception instanceof \Exception) {
                    $exception = new \Exception($exception->getMessage(), $exception->getCode(), $exception);
                }
                $jsonRpcCall->addExceptionItem($exception);
            }
        }
    }
}
