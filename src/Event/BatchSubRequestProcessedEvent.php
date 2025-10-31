<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Event;

/**
 * 批次子请求已处理事件.
 *
 * 仅在 JSON-RPC 调用是批次请求时分发，在子请求已处理后触发
 */
class BatchSubRequestProcessedEvent extends AbstractOnBatchSubRequestProcessEvent
{
}
