<?php

namespace Tourze\JsonRPC\Core\Event;

/**
 * Class OnBatchSubRequestProcessedEvent
 *
 * Dispatched only in case JSON-RPC call is a batch request, after that a sub request has been processed
 */
class BatchSubRequestProcessedEvent extends AbstractOnBatchSubRequestProcessEvent
{
}
