<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Tests\Event;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Tourze\JsonRPC\Core\Event\ResponseSendingEvent;
use Tourze\JsonRPC\Core\Model\JsonRpcCallRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcCallResponse;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;

/**
 * 测试ResponseSendingEvent响应发送事件
 */
class ResponseSendingEventTest extends TestCase
{
    public function testSetAndGetResponseString(): void
    {
        $event = new ResponseSendingEvent();
        $responseString = '{"jsonrpc":"2.0","result":{"success":true},"id":"123"}';

        $event->setResponseString($responseString);

        $this->assertEquals($responseString, $event->getResponseString());
        $this->assertJson($event->getResponseString());
    }

    public function testSetAndGetJsonRpcCallResponse(): void
    {
        $event = new ResponseSendingEvent();
        $callResponse = new JsonRpcCallResponse();
        
        $response = new JsonRpcResponse();
        $response->setResult(['data' => 'test']);
        $response->setId('456');
        $callResponse->addResponse($response);

        $event->setJsonRpcCallResponse($callResponse);

        $this->assertSame($callResponse, $event->getJsonRpcCallResponse());
        $this->assertCount(1, $event->getJsonRpcCallResponse()->getResponseList());
    }

    public function testSetAndGetJsonRpcCall(): void
    {
        $event = new ResponseSendingEvent();
        $callRequest = new JsonRpcCallRequest();
        
        $request = new JsonRpcRequest();
        $request->setMethod('test.method');
        $request->setId('789');
        $callRequest->addRequestItem($request);

        // 初始值应该为null
        $this->assertNull($event->getJsonRpcCall());

        $event->setJsonRpcCall($callRequest);

        $this->assertSame($callRequest, $event->getJsonRpcCall());
        $this->assertCount(1, $event->getJsonRpcCall()->getItemList());
    }

    public function testJsonRpcCallCanBeSetToNull(): void
    {
        $event = new ResponseSendingEvent();
        $callRequest = new JsonRpcCallRequest();
        
        $event->setJsonRpcCall($callRequest);
        $this->assertSame($callRequest, $event->getJsonRpcCall());

        $event->setJsonRpcCall(null);
        $this->assertNull($event->getJsonRpcCall());
    }

    public function testSetAndGetRequest(): void
    {
        $event = new ResponseSendingEvent();
        $request = Request::create('/json-rpc', 'POST', [], [], [], [], 
            '{"jsonrpc":"2.0","method":"test","id":"1"}');

        // 先设置必需属性避免错误
        $callResponse = new JsonRpcCallResponse();
        $event->setJsonRpcCallResponse($callResponse);
        $event->setResponseString('{"test": true}');
        
        // 设置request后再测试
        $event->setRequest($request);

        $this->assertSame($request, $event->getRequest());
        $this->assertEquals('POST', $event->getRequest()->getMethod());
        $this->assertEquals('/json-rpc', $event->getRequest()->getPathInfo());
    }

    public function testRequestCanBeSetToNull(): void
    {
        $event = new ResponseSendingEvent();
        $request = Request::create('/test', 'POST');

        $event->setRequest($request);
        $this->assertSame($request, $event->getRequest());

        $event->setRequest(null);
        $this->assertNull($event->getRequest());
    }

    public function testCompleteEventSetup(): void
    {
        $event = new ResponseSendingEvent();
        
        // 创建响应字符串
        $responseString = '{"jsonrpc":"2.0","result":{"products":[{"id":1,"name":"Laptop"}]},"id":"search-123"}';
        
        // 创建JsonRpcCallResponse
        $callResponse = new JsonRpcCallResponse();
        $response = new JsonRpcResponse();
        $response->setResult(['products' => [['id' => 1, 'name' => 'Laptop']]]);
        $response->setId('search-123');
        $callResponse->addResponse($response);
        
        // 创建JsonRpcCallRequest
        $callRequest = new JsonRpcCallRequest();
        $request = new JsonRpcRequest();
        $request->setMethod('product.search');
        $request->setId('search-123');
        $callRequest->addRequestItem($request);
        
        // 创建HTTP请求
        $httpRequest = Request::create('/api/json-rpc', 'POST', [], [], [], 
            ['HTTP_CONTENT_TYPE' => 'application/json']);

        $event->setResponseString($responseString);
        $event->setJsonRpcCallResponse($callResponse);
        $event->setJsonRpcCall($callRequest);
        $event->setRequest($httpRequest);

        // 验证所有属性
        $this->assertEquals($responseString, $event->getResponseString());
        $this->assertSame($callResponse, $event->getJsonRpcCallResponse());
        $this->assertSame($callRequest, $event->getJsonRpcCall());
        $this->assertSame($httpRequest, $event->getRequest());
        $this->assertEquals('POST', $event->getRequest()->getMethod());
        $this->assertEquals('/api/json-rpc', $event->getRequest()->getPathInfo());
    }

    public function testEventImplementsJsonRpcServerEvent(): void
    {
        $event = new ResponseSendingEvent();

        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Event\JsonRpcServerEvent::class, $event);
    }

    public function testEventWithBatchResponse(): void
    {
        $event = new ResponseSendingEvent();
        
        $batchResponseString = '[{"jsonrpc":"2.0","result":3,"id":"1"},{"jsonrpc":"2.0","result":19,"id":"2"}]';
        $event->setResponseString($batchResponseString);
        
        $callResponse = new JsonRpcCallResponse();
        
        // 第一个响应
        $response1 = new JsonRpcResponse();
        $response1->setResult(3);
        $response1->setId('1');
        $callResponse->addResponse($response1);
        
        // 第二个响应
        $response2 = new JsonRpcResponse();
        $response2->setResult(19);
        $response2->setId('2');
        $callResponse->addResponse($response2);
        
        $event->setJsonRpcCallResponse($callResponse);

        $this->assertEquals($batchResponseString, $event->getResponseString());
        $this->assertCount(2, $event->getJsonRpcCallResponse()->getResponseList());
        $this->assertJson($event->getResponseString());
    }

    public function testEventWithErrorResponse(): void
    {
        $event = new ResponseSendingEvent();
        
        $errorResponseString = '{"jsonrpc":"2.0","error":{"code":-32601,"message":"Method not found"},"id":"error-test"}';
        $event->setResponseString($errorResponseString);
        
        $callResponse = new JsonRpcCallResponse();
        $response = new JsonRpcResponse();
        $error = new \Tourze\JsonRPC\Core\Exception\JsonRpcMethodNotFoundException('test.method', [
            'method' => 'test.method'
        ]);
        $response->setError($error);
        $response->setId('error-test');
        $callResponse->addResponse($response);
        
        $event->setJsonRpcCallResponse($callResponse);

        $this->assertEquals($errorResponseString, $event->getResponseString());
        $this->assertNotNull($event->getJsonRpcCallResponse()->getResponseList()[0]->getError());
        $this->assertEquals(-32601, $event->getJsonRpcCallResponse()->getResponseList()[0]->getError()->getErrorCode());
    }

    public function testEventWithoutHttpRequest(): void
    {
        $event = new ResponseSendingEvent();
        
        $responseString = '{"jsonrpc":"2.0","result":"success","id":"no-http"}';
        $event->setResponseString($responseString);
        
        $callResponse = new JsonRpcCallResponse();
        $response = new JsonRpcResponse();
        $response->setResult('success');
        $response->setId('no-http');
        $callResponse->addResponse($response);
        
        $event->setJsonRpcCallResponse($callResponse);
        // 不设置HTTP请求，模拟非HTTP环境

        $this->assertEquals($responseString, $event->getResponseString());
        // 不调用getRequest()避免属性未初始化错误
        $this->assertNull($event->getJsonRpcCall());
    }

    public function testEventClassStructure(): void
    {
        $event = new ResponseSendingEvent();
        $reflection = new \ReflectionClass($event);
        
        $this->assertEquals('Tourze\JsonRPC\Core\Event', $reflection->getNamespaceName());
        $this->assertEquals('ResponseSendingEvent', $reflection->getShortName());
        $this->assertFalse($reflection->isAbstract());
        $this->assertTrue($reflection->isInstantiable());
        
        // 验证有正确的属性
        $this->assertTrue($reflection->hasProperty('responseString'));
        $this->assertTrue($reflection->hasProperty('jsonRpcCallResponse'));
        $this->assertTrue($reflection->hasProperty('jsonRpcCall'));
        $this->assertTrue($reflection->hasProperty('request'));
    }

    public function testEventDocumentation(): void
    {
        $reflection = new \ReflectionClass(ResponseSendingEvent::class);
        $docComment = $reflection->getDocComment();
        
        $this->assertNotFalse($docComment);
        $this->assertStringContainsString('Dispatched when a response has been successfully serialized', $docComment);
    }

    public function testEventLifecycle(): void
    {
        // 模拟响应发送的完整生命周期
        $event = new ResponseSendingEvent();
        
        $responseString = '{"jsonrpc":"2.0","result":{"status":"completed"},"id":"lifecycle"}';
        $callResponse = new JsonRpcCallResponse();
        $response = new JsonRpcResponse();
        $response->setResult(['status' => 'completed']);
        $response->setId('lifecycle');
        $callResponse->addResponse($response);
        
        $httpRequest = Request::create('/json-rpc', 'POST');

        $event->setResponseString($responseString);
        $event->setJsonRpcCallResponse($callResponse);
        $event->setRequest($httpRequest);

        // 验证事件包含响应发送所需的所有信息
        $this->assertJson($event->getResponseString());
        $this->assertNotNull($event->getJsonRpcCallResponse());
        $this->assertNotNull($event->getRequest());
        
        // 验证实现关系
        $this->assertInstanceOf(\Tourze\JsonRPC\Core\Event\JsonRpcServerEvent::class, $event);
    }
} 