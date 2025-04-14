<?php

namespace Tourze\JsonRPC\Core\Event;

use Carbon\CarbonInterface;

/**
 * Class OnMethodSuccessEvent
 *
 * Dispatched only in case JSON-RPC method has been successfully executed.
 */
class MethodExecuteSuccessEvent extends AbstractOnMethodEvent
{
    private mixed $result = null;

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function setResult(mixed $result): self
    {
        $this->result = $result;

        return $this;
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
