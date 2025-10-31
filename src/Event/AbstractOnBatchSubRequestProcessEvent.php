<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Event;

/**
 * 批次子请求处理事件抽象类.
 */
abstract class AbstractOnBatchSubRequestProcessEvent implements JsonRpcServerEvent
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
