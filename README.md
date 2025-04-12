# JSON-RPC Core

本库提供符合 [JSON-RPC 2.0 规范](https://www.jsonrpc.org/specification) 的核心组件，用于构建 JSON-RPC 服务器和客户端。

## 安装

```bash
composer require tourze/json-rpc-core
```

## 特性

- 完全符合 JSON-RPC 2.0 规范
- 支持所有 JSON-RPC 请求类型：单个请求、批量请求和通知
- 提供错误处理机制，包括所有标准 JSON-RPC 错误类型
- 灵活的方法解析接口
- 清晰的面向对象 API

## 组件

本库包含以下主要组件：

### 模型类

- `JsonRpcRequest` - 表示一个 JSON-RPC 请求
- `JsonRpcResponse` - 表示一个 JSON-RPC 响应
- `JsonRpcParams` - 包装请求参数
- `JsonRpcCallRequest` - 处理批量请求
- `JsonRpcCallResponse` - 处理批量响应

### 领域接口

- `JsonRpcMethodInterface` - 定义 JSON-RPC 方法的接口
- `JsonRpcMethodResolverInterface` - 解析方法名到具体方法实现
- `JsonRpcMethodParamsValidatorInterface` - 验证方法参数
- `MethodWithValidatedParamsInterface` - 带有参数验证的方法
- `MethodWithResultDocInterface` - 带有结果文档的方法

### 异常类

- `JsonRpcException` - 基础 JSON-RPC 异常
- `JsonRpcParseErrorException` - 解析错误异常
- `JsonRpcInvalidRequestException` - 无效请求异常
- `JsonRpcMethodNotFoundException` - 方法未找到异常
- `JsonRpcInvalidParamsException` - 无效参数异常
- `JsonRpcInternalErrorException` - 内部错误异常

## 使用示例

### 创建一个 JSON-RPC 方法

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
} catch (\Exception $e) {
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
