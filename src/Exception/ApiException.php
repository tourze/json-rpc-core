<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Exception;

use Tourze\BacktraceHelper\ContextAwareInterface;

/**
 * JSON-RPC 执行过程中, 可统一对外的异常
 * 适用范围: RPC的procedures中可抛出.
 */
class ApiException extends JsonRpcException implements ContextAwareInterface
{
    /**
     * @param string|array{0: int, 1: string} $mixed
     * @param array<string, mixed>            $data
     */
    public function __construct(string|array $mixed = '', int $code = 0, array $data = [], ?\Throwable $previous = null)
    {
        if (is_array($mixed)) {
            $message = $mixed[1];
            $code = $mixed[0];
        } else {
            $message = $mixed;
        }

        parent::__construct($code, $message, $data, previous: $previous);
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->getErrorData();
    }

    /**
     * @param array<string, mixed> $context
     */
    public function setContext(array $context): void
    {
        $this->setErrorData($context);
    }
}
