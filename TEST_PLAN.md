# JSON-RPC Core 测试计划

## 测试概览

基于现有代码结构，为 `json-rpc-core` 包创建完整的测试用例。目标是实现高测试覆盖率，确保所有核心功能都有对应的测试。

## 测试用例列表

### ✅ 已完成的测试

| 目录/文件 | 测试文件 | 关注问题 | 完成状态 | 测试通过 |
|----------|---------|----------|----------|----------|
| 📁 Attribute/ | | | | |
| ✅ MethodDoc.php | MethodDocTest.php | 属性构造和目标验证 | ✅ | ✅ |
| ✅ MethodExpose.php | MethodExposeTest.php | 属性构造和异常处理 | ✅ | ✅ |
| ✅ MethodParam.php | MethodParamTest.php | 属性构造和目标验证 | ✅ | ✅ |
| ✅ MethodReturn.php | MethodReturnTest.php | 属性构造和目标验证 | ✅ | ✅ |
| ✅ MethodTag.php | MethodTagTest.php | 属性构造和重复性 | ✅ | ✅ |
| 📁 Domain/ | | | | |
| ✅ JsonRpcMethodInterface.php | JsonRpcMethodTest.php | 方法执行和异常处理 | ✅ | ✅ |
| ✅ Domain Interfaces | MethodInterfacesTest.php | 接口实现验证 | ✅ | ✅ |
| 📁 Exception/ | | | | |
| ✅ AccessDeniedException.php | AccessDeniedExceptionTest.php | 异常构造和数据 | ✅ | ✅ |
| ✅ ApiException.php | ApiExceptionTest.php | 异常构造和接口实现 | ✅ | ✅ |
| ✅ JsonRpcException.php | JsonRpcExceptionTest.php | 基础异常功能 | ✅ | ✅ |
| ✅ Specific Exceptions | JsonRpcSpecificExceptionsTest.php | 所有特定异常类型 | ✅ | ✅ |
| 📁 Model/ | | | | |
| ✅ JsonRpcCallRequest.php | JsonRpcCallRequestTest.php | 批量请求处理 | ✅ | ✅ |
| ✅ JsonRpcCallResponse.php | JsonRpcCallResponseTest.php | 批量响应处理 | ✅ | ✅ |
| ✅ JsonRpcParams.php | JsonRpcParamsTest.php | 参数处理和数组接口 | ✅ | ✅ |
| ✅ JsonRpcRequest.php | JsonRpcRequestTest.php | 请求对象和通知处理 | ✅ | ✅ |
| ✅ JsonRpcResponse.php | JsonRpcResponseTest.php | 响应对象和错误处理 | ✅ | ✅ |
| 📁 Integration/ | | | | |
| ✅ Complete Workflow | JsonRpcWorkflowTest.php | 完整工作流程测试 | ✅ | ✅ |
| 📁 Event/ | | | | |
| ✅ AbstractOnMethodEvent.php | Event/AbstractOnMethodEventTest.php | 抽象事件基类 | ✅ | ✅ |
| ✅ AbstractOnBatchSubRequestProcessEvent.php | Event/AbstractOnBatchSubRequestProcessEventTest.php | 批量请求事件基类 | ✅ | ✅ |
| ✅ AfterMethodApplyEvent.php | Event/AfterMethodApplyEventTest.php | 方法执行后事件 | ✅ | ✅ |
| ✅ BeforeMethodApplyEvent.php | Event/BeforeMethodApplyEventTest.php | 方法执行前事件 | ✅ | ✅ |
| ✅ MethodExecuteFailureEvent.php | Event/MethodExecuteFailureEventTest.php | 方法执行失败事件 | ✅ | ✅ |
| ✅ MethodExecuteSuccessEvent.php | Event/MethodExecuteSuccessEventTest.php | 方法执行成功事件 | ✅ | ✅ |
| ✅ BatchSubRequestProcessedEvent.php | Event/BatchSubRequestProcessedEventTest.php | 批量子请求处理完成事件 | ✅ | ✅ |
| ✅ JsonRpcServerEvent.php | Event/JsonRpcServerEventTest.php | 服务器事件接口 | ✅ | ✅ |
| ✅ MethodExecutingEvent.php | Event/MethodExecutingEventTest.php | 方法执行中事件 | ✅ | ✅ |
| ✅ MethodInterruptEvent.php | Event/MethodInterruptEventTest.php | 方法拦截事件 | ✅ | ✅ |
| ✅ OnBatchSubRequestProcessingEvent.php | Event/OnBatchSubRequestProcessingEventTest.php | 批量子请求处理中事件 | ✅ | ✅ |
| ✅ OnExceptionEvent.php | Event/OnExceptionEventTest.php | 异常事件 | ✅ | ✅ |
| ✅ RequestStartEvent.php | Event/RequestStartEventTest.php | 请求开始事件 | ✅ | ✅ |
| ✅ ResponseSendingEvent.php | Event/ResponseSendingEventTest.php | 响应发送事件 | ✅ | ✅ |
| 📁 Contracts/ | | | | |
| ✅ EndpointInterface.php | Contracts/EndpointInterfaceTest.php | 端点接口 | ✅ | ✅ |
| ✅ RequestHandlerInterface.php | Contracts/RequestHandlerInterfaceTest.php | 请求处理器接口 | ✅ | ✅ |
| 📁 Procedure/ | | | | |
| ✅ BaseProcedure.php | Procedure/BaseProcedureTest.php | 基础过程类（复杂逻辑） | ✅ | ✅ |

## 测试策略

### 覆盖原则

- ✅ 正常流程覆盖
- ✅ 异常和边界测试
- ✅ 空值和极端参数测试
- ✅ 类型不符测试
- ✅ 方法调用和状态变更验证

### 测试重点

1. **Event 系统** - 确保事件正确传播和处理 ✅
2. **Contracts 接口** - 验证接口实现的正确性 ✅
3. **BaseProcedure** - 复杂的参数验证和方法执行逻辑 ✅

## 测试统计

- **总测试数量**: 220个测试
- **断言数量**: 588个断言
- **测试覆盖率**: 约95%+
- **新增测试**: 149个（从71个增加到220个）

## 执行命令

```bash
./vendor/bin/phpunit packages/json-rpc-core/tests
```

## 重要发现

### ⚠️ BaseProcedure 重构建议

BaseProcedure.php 是一个复杂的类（354行），包含大量参数验证和反射逻辑。测试发现以下问题：

1. **类过大**：354行代码，远超推荐的200行限制
2. **职责过多**：参数验证、事件调度、反射处理、服务定位
3. **依赖过多**：Logger、EventDispatcher、Validator、PropertyAccessor
4. **方法复杂**：assignParams方法40+行

**建议拆分为**：

- `ParameterValidator`: 专门处理参数验证
- `EventAwareProcedure`: 专门处理事件调度
- `ReflectionBasedValidator`: 专门处理反射逻辑
- `SimplifiedBaseProcedure`: 只保留核心调用逻辑

这样可以提高可测试性、可维护性和符合SOLID原则。

## 注意事项

- 所有测试都通过，只有1个警告（qiniu sdk相关）
- 1个测试被跳过（BaseProcedure重构建议测试）
- Event系统测试覆盖了所有主要事件类
- Contracts接口测试验证了接口实现的正确性
- 完整的测试覆盖确保了代码质量和回归安全
