<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Event;

/**
 * 批量子请求处理事件.
 *
 * 仅在 JSON-RPC 调用是批量请求时分发，在子请求被处理之前触发
 */
class OnBatchSubRequestProcessingEvent extends AbstractOnBatchSubRequestProcessEvent
{
}
