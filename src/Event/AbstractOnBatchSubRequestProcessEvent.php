<?php

namespace Tourze\JsonRPC\Core\Event;

/**
 * Class AbstractOnBatchSubRequestProcessEvent
 */
class AbstractOnBatchSubRequestProcessEvent implements JsonRpcServerEvent
{
    private int $itemPosition;

    public function getItemPosition(): int
    {
        return $this->itemPosition;
    }

    public function setItemPosition(int $itemPosition): void
    {
        $this->itemPosition = $itemPosition;
    }
}
