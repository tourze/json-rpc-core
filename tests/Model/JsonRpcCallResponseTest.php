<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Model;

use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Model\JsonRpcCallResponse;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;

class JsonRpcCallResponseTest extends TestCase
{
    public function testNonBatchResponse(): void
    {
        $callResponse = new JsonRpcCallResponse(false);

        $this->assertFalse($callResponse->isBatch());
        $this->assertEmpty($callResponse->getResponseList());
    }

    public function testBatchResponse(): void
    {
        $callResponse = new JsonRpcCallResponse(true);

        $this->assertTrue($callResponse->isBatch());
    }

    public function testAddResponse(): void
    {
        $callResponse = new JsonRpcCallResponse();

        $response1 = new JsonRpcResponse();
        $response1->setResult(['data' => 'result1']);
        $response1->setId(1);

        $response2 = new JsonRpcResponse();
        $response2->setError(new JsonRpcException(100, 'Test error'));
        $response2->setId(2);

        $callResponse->addResponse($response1);
        $callResponse->addResponse($response2);

        $responses = $callResponse->getResponseList();

        $this->assertCount(2, $responses);
        $this->assertSame($response1, $responses[0]);
        $this->assertSame($response2, $responses[1]);
    }

    public function testAddMultipleResponses(): void
    {
        $callResponse = new JsonRpcCallResponse(true);

        $responses = [];
        for ($i = 0; $i < 5; $i++) {
            $response = new JsonRpcResponse();
            $response->setResult("Result $i");
            $response->setId($i);

            $callResponse->addResponse($response);
            $responses[] = $response;
        }

        $responseList = $callResponse->getResponseList();

        $this->assertCount(5, $responseList);
        foreach ($responses as $index => $response) {
            $this->assertSame($response, $responseList[$index]);
        }
    }
}
