<?php

namespace Tourze\JsonRPC\Core\Event;

/**
 * Class OnBatchSubRequestProcessingEvent
 *
 * Dispatched only in case JSON-RPC call is a batch request, before that a sub request will be processed
 */
class OnBatchSubRequestProcessingEvent extends AbstractOnBatchSubRequestProcessEvent
{
}
