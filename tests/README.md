# JSON-RPC Core 测试

此目录包含 `json-rpc-core` 包的测试套件。测试使用 PHPUnit 进行，涵盖了库的不同组件。

## 测试结构

测试目录结构与源代码目录结构相匹配：

- `Exception/` - 测试异常类
- `Model/` - 测试模型类
- `Domain/` - 测试领域接口
- `Integration/` - 集成测试，测试完整的 JSON-RPC 工作流程

## 运行测试

在项目根目录执行以下命令运行测试：

```bash
vendor/bin/phpunit -c packages/json-rpc-core/phpunit.xml.dist
```

或者使用 Composer 脚本：

```bash
cd packages/json-rpc-core
composer test
```

## 测试内容

### 模型测试

这些测试验证核心模型类（如 `JsonRpcRequest`、`JsonRpcResponse` 等）的行为是否正确。测试包括：

- 属性的 getter 和 setter
- 对象的状态转换
- 参数处理
- 对象间的关系

### 异常测试

这些测试验证 JSON-RPC 异常类是否按预期工作，包括：

- 错误代码和消息
- 特定类型异常的构造
- 异常层次结构

### 接口测试

这些测试确保接口定义符合 JSON-RPC 2.0 规范。

### 集成测试

集成测试验证完整的 JSON-RPC 工作流程，包括：

- 单个请求处理
- 批量请求处理
- 通知请求处理
- 方法调用和错误处理
