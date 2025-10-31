# JSON-RPC Core

[![PHP Version](https://img.shields.io/badge/php-8.1%2B-blue.svg)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)](#测试)
[![Code Coverage](https://img.shields.io/badge/coverage-95%25-brightgreen.svg)](#测试)

[English](README.md) | [中文](README.zh-CN.md)

本库为 PHP 提供符合 [JSON-RPC 2.0 规范](https://www.jsonrpc.org/specification) 
的核心组件，可用于构建高效、标准化的 JSON-RPC 服务器与客户端。

## 目录

- [特性](#特性)
- [安装](#安装)
- [组件说明](#组件说明)
- [使用示例](#使用示例)
- [高级用法](#高级用法)
- [测试](#测试)
- [贡献与支持](#贡献与支持)
- [License](#license)

## 特性

- 完全遵循 JSON-RPC 2.0 规范
- 支持单个请求、批量请求与通知类型
- 完善的错误处理机制，覆盖所有标准错误类型
- 灵活的方法解析与参数校验接口
- 清晰易用的面向对象 API

## 安装

```bash
composer require tourze/json-rpc-core
```

**依赖要求：**
- PHP 8.1+
- nesbot/carbon ^2.72 || ^3
- psr/log ^1|^2|^3
- symfony/dependency-injection ^7.3
- symfony/event-dispatcher-contracts ^3
- symfony/http-foundation ^7.3
- symfony/property-access ^7.3
- symfony/service-contracts ^3.6
- symfony/validator ^7.3
- tourze/arrayable 0.0.*
- tourze/backtrace-helper 0.1.*

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

## 高级用法

### 自定义参数验证

使用 `BaseProcedure` 类进行自动参数验证：

```php
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Symfony\Component\Validator\Constraints as Assert;

class CalculatorMethod extends BaseProcedure
{
    #[MethodParam('第一个数字')]
    #[Assert\Type('numeric')]
    #[Assert\NotBlank]
    public int $a;

    #[MethodParam('第二个数字')]
    #[Assert\Type('numeric')]
    #[Assert\NotBlank]
    public int $b;

    public function execute(): int
    {
        return $this->a + $this->b;
    }
}
```

### 事件驱动架构

监听 JSON-RPC 事件进行日志和监控：

```php
use Tourze\JsonRPC\Core\Event\BeforeMethodApplyEvent;
use Tourze\JsonRPC\Core\Event\AfterMethodApplyEvent;

// 方法执行前
$dispatcher->addListener(BeforeMethodApplyEvent::class, function ($event) {
    $logger->info('方法调用', [
        'method' => $event->getName(),
        'params' => $event->getParams()->all()
    ]);
});

// 方法执行后
$dispatcher->addListener(AfterMethodApplyEvent::class, function ($event) {
    $logger->info('方法完成', [
        'method' => $event->getName(),
        'result' => $event->getResult()
    ]);
});
```

### 复杂验证的辅助类

本包包含用于高级参数处理的辅助类：

- `TypeValidatorFactory`: 从反射创建类型验证器
- `PropertyConstraintExtractor`: 从属性提取验证约束
- `ParameterProcessor`: 处理参数赋值和验证

## 测试

```bash
vendor/bin/phpunit packages/json-rpc-core/tests
```

## 贡献与支持

欢迎提交 Issue 或 PR 参与改进。如需更多帮助，请参考源码或联系维护者。

## License

本项目采用 MIT 许可证 - 详情请参阅 [LICENSE](LICENSE) 文件。
