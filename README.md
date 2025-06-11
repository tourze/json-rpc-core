# JSON-RPC Core

This library provides core components compliant with the [JSON-RPC 2.0 specification](https://www.jsonrpc.org/specification) for building JSON-RPC servers and clients in PHP.

## Installation

```bash
composer require tourze/json-rpc-core
```

## Features

- Fully compliant with JSON-RPC 2.0 specification
- Supports all JSON-RPC request types: single, batch, and notification
- Robust error handling for all standard JSON-RPC error types
- Flexible method resolution interfaces
- Clean object-oriented API

## Components

### Models

- `JsonRpcRequest` - Represents a JSON-RPC request
- `JsonRpcResponse` - Represents a JSON-RPC response
- `JsonRpcParams` - Wraps request parameters
- `JsonRpcCallRequest` - Handles batch requests
- `JsonRpcCallResponse` - Handles batch responses

### Domain Interfaces

- `JsonRpcMethodInterface` - Defines the interface for JSON-RPC methods
- `JsonRpcMethodResolverInterface` - Resolves method names to implementations
- `JsonRpcMethodParamsValidatorInterface` - Validates method parameters
- `MethodWithValidatedParamsInterface` - Methods with parameter validation
- `MethodWithResultDocInterface` - Methods with result documentation

### Exceptions

- `JsonRpcException` - Base JSON-RPC exception
- `JsonRpcParseErrorException` - Parse error exception
- `JsonRpcInvalidRequestException` - Invalid request exception
- `JsonRpcMethodNotFoundException` - Method not found exception
- `JsonRpcInvalidParamsException` - Invalid params exception
- `JsonRpcInternalErrorException` - Internal error exception

## Usage Example

### Creating a JSON-RPC Method

```php
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

class EchoMethod implements JsonRpcMethodInterface
{
    public function __invoke(JsonRpcRequest $request): mixed
    {
        return $request->getParams()->all();
    }

    public function execute(): array
    {
        return [];
    }
}
```

### 处理 JSON-RPC 请求

```php
use Tourze\JsonRPC\Core\Exception\JsonRpcException;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPC\Core\Model\JsonRpcResponse;

// 创建请求
$request = new JsonRpcRequest();
$request->setJsonrpc('2.0');
$request->setMethod('echo');
$request->setId('1');
$request->setParams(new JsonRpcParams(['message' => 'Hello, World!']));

// 创建方法实例
$method = new EchoMethod();

// 执行方法并获取响应
$response = new JsonRpcResponse();
$response->setJsonrpc('2.0');
$response->setId($request->getId());

try {
    $result = $method($request);
    $response->setResult($result);
} catch (JsonRpcException $e) {
    $response->setError($e);
} catch (\Throwable $e) {
    // 将普通异常包装为 JSON-RPC 异常
    $jsonRpcException = new JsonRpcException(-32000, $e->getMessage());
    $response->setError($jsonRpcException);
}
```

### 批量请求

通过 `JsonRpcCallRequest` 和 `JsonRpcCallResponse` 处理批量请求。详见单元测试中的示例。

## 测试

```bash
vendor/bin/phpunit -c packages/json-rpc-core/phpunit.xml.dist
```
