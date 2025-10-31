# JSON-RPC Core

[![PHP Version](https://img.shields.io/badge/php-8.1%2B-blue.svg)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)](#testing)
[![Code Coverage](https://img.shields.io/badge/coverage-95%25-brightgreen.svg)](#testing)

[English](README.md) | [中文](README.zh-CN.md)

This library provides core components compliant with the 
[JSON-RPC 2.0 specification](https://www.jsonrpc.org/specification) 
for building JSON-RPC servers and clients in PHP.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Components](#components)
- [Usage Example](#usage-example)
- [Advanced Usage](#advanced-usage)
- [Testing](#testing)
- [License](#license)

## Features

- Fully compliant with JSON-RPC 2.0 specification
- Supports all JSON-RPC request types: single, batch, and notification
- Robust error handling for all standard JSON-RPC error types
- Flexible method resolution interfaces
- Clean object-oriented API

## Installation

```bash
composer require tourze/json-rpc-core
```

**Requirements:**
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

## Advanced Usage

### Custom Parameter Validation

Use the `BaseProcedure` class for automatic parameter validation:

```php
use Tourze\JsonRPC\Core\Procedure\BaseProcedure;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Symfony\Component\Validator\Constraints as Assert;

class CalculatorMethod extends BaseProcedure
{
    #[MethodParam('First number')]
    #[Assert\Type('numeric')]
    #[Assert\NotBlank]
    public int $a;

    #[MethodParam('Second number')]
    #[Assert\Type('numeric')]
    #[Assert\NotBlank]
    public int $b;

    public function execute(): int
    {
        return $this->a + $this->b;
    }
}
```

### Event-Driven Architecture

Listen to JSON-RPC events for logging and monitoring:

```php
use Tourze\JsonRPC\Core\Event\BeforeMethodApplyEvent;
use Tourze\JsonRPC\Core\Event\AfterMethodApplyEvent;

// Before method execution
$dispatcher->addListener(BeforeMethodApplyEvent::class, function ($event) {
    $logger->info('Method called', [
        'method' => $event->getName(),
        'params' => $event->getParams()->all()
    ]);
});

// After method execution
$dispatcher->addListener(AfterMethodApplyEvent::class, function ($event) {
    $logger->info('Method completed', [
        'method' => $event->getName(),
        'result' => $event->getResult()
    ]);
});
```

### Helper Classes for Complex Validation

The package includes helper classes for advanced parameter processing:

- `TypeValidatorFactory`: Creates type validators from reflection
- `PropertyConstraintExtractor`: Extracts validation constraints from properties
- `ParameterProcessor`: Handles parameter assignment and validation

## Testing

```bash
vendor/bin/phpunit packages/json-rpc-core/tests
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
