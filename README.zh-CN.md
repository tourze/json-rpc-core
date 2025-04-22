# JSON-RPC Core

本库为 PHP 提供符合 [JSON-RPC 2.0 规范](https://www.jsonrpc.org/specification) 的核心组件，可用于构建高效、标准化的 JSON-RPC 服务器与客户端。

## 安装

```bash
composer require tourze/json-rpc-core
```

## 特性

- 完全遵循 JSON-RPC 2.0 规范
- 支持单个请求、批量请求与通知类型
- 完善的错误处理机制，覆盖所有标准错误类型
- 灵活的方法解析与参数校验接口
- 清晰易用的面向对象 API

## 组件说明

### 模型类

- `JsonRpcRequest` —— 表示 JSON-RPC 请求
- `JsonRpcResponse` —— 表示 JSON-RPC 响应
- `JsonRpcParams` —— 封装请求参数
- `JsonRpcCallRequest` —— 批量请求处理
- `JsonRpcCallResponse` —— 批量响应处理

### 领域接口

- `JsonRpcMethodInterface` —— JSON-RPC 方法接口定义
- `JsonRpcMethodResolverInterface` —— 方法名到实现的解析接口
- `JsonRpcMethodParamsValidatorInterface` —— 方法参数校验接口
- `MethodWithValidatedParamsInterface` —— 带参数校验的方法接口
- `MethodWithResultDocInterface` —— 带结果文档说明的方法接口

### 异常类

- `JsonRpcException` —— 基础异常
- `JsonRpcParseErrorException` —— 解析错误
- `JsonRpcInvalidRequestException` —— 无效请求
- `JsonRpcMethodNotFoundException` —— 方法未找到
- `JsonRpcInvalidParamsException` —— 参数无效
- `JsonRpcInternalErrorException` —— 内部错误

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
}
```

## 工作流程

本库支持标准 JSON-RPC 2.0 的全部流程，包括请求解析、方法分发、参数校验、异常处理、响应生成等。复杂流程可参考[流程图](./json-rpc-core-flow.md)。

## 贡献与支持

欢迎提交 Issue 或 PR 参与改进。如需更多帮助，请参考源码或联系维护者。
