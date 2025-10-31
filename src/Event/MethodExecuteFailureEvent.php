<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Event;

use Carbon\CarbonInterface;

/**
 * Class OnMethodFailureEvent.
 *
 * Dispatched only in case JSON-RPC method throw an exception during execution
 */
class MethodExecuteFailureEvent extends AbstractOnMethodEvent
{
    private \Throwable $exception;

    public function getException(): \Throwable
    {
        return $this->exception;
    }

    public function setException(\Throwable $exception): void
    {
        $this->exception = $exception;
    }

    private CarbonInterface $startTime;

    public function getStartTime(): CarbonInterface
    {
        return $this->startTime;
    }

    public function setStartTime(CarbonInterface $startTime): void
    {
        $this->startTime = $startTime;
    }

    private CarbonInterface $endTime;

    public function getEndTime(): CarbonInterface
    {
        return $this->endTime;
    }

    public function setEndTime(CarbonInterface $endTime): void
    {
        $this->endTime = $endTime;
    }
}
