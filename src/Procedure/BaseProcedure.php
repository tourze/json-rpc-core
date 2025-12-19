<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Procedure;

use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceMethodsSubscriberTrait;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithResultDocInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithValidatedParamsInterface;
use Tourze\JsonRPC\Core\Event\AfterMethodApplyEvent;
use Tourze\JsonRPC\Core\Event\BeforeMethodApplyEvent;
use Tourze\JsonRPC\Core\Helper\ParamObjectFactory;
use Tourze\JsonRPC\Core\Helper\PropertyConstraintExtractor;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * JsonRPC Procedure 基类
 *
 * 纯参数对象模式：
 * - 所有 Procedure 必须实现 execute(XxxParam $param) 方法签名
 * - 框架通过反射检测 execute 方法的参数类型
 * - 自动将请求数据反序列化为参数对象并注入
 */
#[MethodTag(name: 'base')]
#[MethodDoc(summary: 'Base Procedure', description: 'Abstract base class for JSON-RPC procedures')]
#[MethodExpose(method: 'base.procedure')]
abstract class BaseProcedure implements JsonRpcMethodInterface, MethodWithValidatedParamsInterface, MethodWithResultDocInterface, ServiceSubscriberInterface
{
    use ServiceMethodsSubscriberTrait;

    #[SubscribedService]
    private function getBaseProcedureLogger(): LoggerInterface
    {
        /** @var LoggerInterface $logger */
        $logger = $this->container->get(__METHOD__);

        return $logger;
    }

    #[SubscribedService]
    private function getEventDispatcher(): EventDispatcherInterface
    {
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->container->get(__METHOD__);

        return $dispatcher;
    }

    #[SubscribedService]
    private function getValidator(): ValidatorInterface
    {
        /** @var ValidatorInterface $validator */
        $validator = $this->container->get(__METHOD__);

        return $validator;
    }

    #[SubscribedService]
    private function getSerializer(): SerializerInterface
    {
        /** @var SerializerInterface $serializer */
        $serializer = $this->container->get(__METHOD__);

        return $serializer;
    }

    /**
     * 获取参数对象工厂
     */
    private function getParamObjectFactory(): ParamObjectFactory
    {
        return new ParamObjectFactory(
            $this->getSerializer(),
            $this->getValidator()
        );
    }

    /**
     * 检测 execute 方法的参数类
     *
     * 支持以下签名格式：
     * - execute(ConcreteParam $param)                    -> ConcreteParam
     * - execute(ConcreteParam|RpcParamInterface $param)  -> ConcreteParam
     *
     * @return class-string<RpcParamInterface> 参数类名
     *
     * @throws \RuntimeException 如果 execute 方法签名不正确
     */
    private function detectParamClass(): string
    {
        $method = new \ReflectionMethod($this, 'execute');
        $params = $method->getParameters();
        $procedureClass = $method->getDeclaringClass()->getName();

        if (0 === count($params)) {
            throw new \RuntimeException(sprintf(
                'Procedure %s 的 execute 方法必须声明一个 RpcParamInterface 类型的参数',
                $procedureClass
            ));
        }

        $type = $params[0]->getType();

        if ($type instanceof \ReflectionUnionType) {
            return $this->extractFromUnionType($type, $procedureClass);
        }

        return $this->extractFromNamedType($type, $procedureClass);
    }

    /**
     * @return class-string<RpcParamInterface>
     */
    private function extractFromUnionType(\ReflectionUnionType $type, string $procedureClass): string
    {
        foreach ($type->getTypes() as $unionType) {
            if (!$unionType instanceof \ReflectionNamedType) {
                continue;
            }
            $typeName = $unionType->getName();
            if (RpcParamInterface::class !== $typeName && is_a($typeName, RpcParamInterface::class, true)) {
                /** @var class-string<RpcParamInterface> $typeName */
                return $typeName;
            }
        }

        throw new \RuntimeException(sprintf(
            'Procedure %s 的 execute 方法联合类型参数中未找到具体的 RpcParamInterface 实现类',
            $procedureClass
        ));
    }

    /**
     * @return class-string<RpcParamInterface>
     */
    private function extractFromNamedType(?\ReflectionType $type, string $procedureClass): string
    {
        if (!$type instanceof \ReflectionNamedType) {
            throw new \RuntimeException(sprintf(
                'Procedure %s 的 execute 方法第一个参数必须有类型声明',
                $procedureClass
            ));
        }

        $className = $type->getName();

        if (RpcParamInterface::class === $className) {
            throw new \RuntimeException(sprintf(
                'Procedure %s 的 execute 方法参数不能使用纯 RpcParamInterface 接口类型，请使用具体 Param 类或联合类型 ConcreteParam|RpcParamInterface',
                $procedureClass
            ));
        }

        if (!is_a($className, RpcParamInterface::class, true)) {
            throw new \RuntimeException(sprintf(
                'Procedure %s 的 execute 方法参数类型 %s 必须实现 RpcParamInterface',
                $procedureClass,
                $className
            ));
        }

        /** @var class-string<RpcParamInterface> $className */
        return $className;
    }

    /**
     * 创建参数对象
     *
     * @template T of RpcParamInterface
     *
     * @param class-string<T> $className
     *
     * @return T
     */
    private function createParamObject(string $className, JsonRpcParams $params): RpcParamInterface
    {
        return $this->getParamObjectFactory()->create($className, $params->toArray());
    }

    public function __invoke(JsonRpcRequest $request): RpcResultInterface
    {
        // 执行前触发
        $beforeEvent = new BeforeMethodApplyEvent();
        $beforeEvent->setMethod($this);
        $beforeEvent->setRequest($request);
        $beforeEvent->setName($request->getMethod());
        $beforeEvent->setParams($request->getParams() ?? new JsonRpcParams());
        $this->getEventDispatcher()->dispatch($beforeEvent);
        $beforeResult = $beforeEvent->getResult();
        if ($beforeResult instanceof RpcResultInterface) {
            $this->getBaseProcedureLogger()->debug('执行前直接返回结果', ['result' => $beforeResult]);

            return $beforeResult;
        }

        // 检测参数类并创建参数对象
        $paramClass = $this->detectParamClass();
        $param = $this->createParamObject($paramClass, $beforeEvent->getParams());
        $res = $this->execute($param);

        // 执行后触发
        $afterEvent = new AfterMethodApplyEvent();
        $afterEvent->setMethod($this);
        $afterEvent->setRequest($request);
        $afterEvent->setName($request->getMethod());
        $afterEvent->setParams($beforeEvent->getParams());
        $afterEvent->setResult($res);
        $this->getEventDispatcher()->dispatch($afterEvent);

        $afterResult = $afterEvent->getResult();
        if ($afterResult instanceof RpcResultInterface) {
            return $afterResult;
        }

        return $res;
    }

    /**
     * 执行 Procedure 逻辑
     *
     * 子类必须实现此方法，签名为 execute(XxxParam $param): XxxResult
     * 其中 XxxParam 必须实现 RpcParamInterface
     * 其中 XxxResult 必须实现 RpcResultInterface
     */
    abstract public function execute(RpcParamInterface $param): RpcResultInterface;

    /**
     * 根据参数对象类的定义，自动生成验证规则
     *
     * 从参数类的构造器参数中提取验证约束
     */
    public function getParamsConstraint(): Collection
    {
        $fields = [];

        try {
            $paramClass = $this->detectParamClass();
            $reflection = new \ReflectionClass($paramClass);
            $constructor = $reflection->getConstructor();

            if (null !== $constructor) {
                foreach ($constructor->getParameters() as $parameter) {
                    $constraint = PropertyConstraintExtractor::extractConstraintFromParameter($parameter);
                    if (null !== $constraint) {
                        $fields[$parameter->getName()] = $constraint;
                    }
                }
            }
        } catch (\RuntimeException) {
            // 如果无法检测参数类，返回空约束
        }

        return new Collection($fields, allowExtraFields: true, allowMissingFields: true);
    }

    /**
     * 获取指定参数的文档描述
     *
     * 从参数对象类的构造器参数中提取文档元数据
     *
     * @return array<string, mixed>|null
     */
    public function getPropertyDocument(string $propertyName): ?array
    {
        try {
            $paramClass = $this->detectParamClass();
            $reflection = new \ReflectionClass($paramClass);
            $constructor = $reflection->getConstructor();

            if (null === $constructor) {
                return null;
            }

            foreach ($constructor->getParameters() as $parameter) {
                if ($parameter->getName() !== $propertyName) {
                    continue;
                }

                return $this->extractParameterDocument($parameter);
            }
        } catch (\RuntimeException) {
            // 如果无法检测参数类，返回 null
        }

        return null;
    }

    /**
     * 从构造器参数提取文档元数据
     *
     * @return array<string, mixed>|null
     */
    private function extractParameterDocument(\ReflectionParameter $parameter): ?array
    {
        $methodParamAttrs = $parameter->getAttributes(MethodParam::class);
        if ([] === $methodParamAttrs) {
            return null;
        }

        $methodParam = $methodParamAttrs[0]->newInstance();
        assert($methodParam instanceof MethodParam);

        $reflectionType = $parameter->getType();
        $type = ($reflectionType instanceof \ReflectionNamedType)
            ? $reflectionType->getName()
            : 'mixed';

        $min = $max = null;
        foreach ($parameter->getAttributes() as $attribute) {
            if (!is_subclass_of($attribute->getName(), Constraint::class)) {
                continue;
            }

            if (Range::class === $attribute->getName()) {
                $instance = $attribute->newInstance();
                assert($instance instanceof Range);
                $min = $instance->min;
                $max = $instance->max;
            }
        }

        $default = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;

        return [
            'name' => $parameter->getName(),
            'type' => $type,
            'description' => $methodParam->description,
            'default' => $default,
            'nullable' => $parameter->isDefaultValueAvailable() || ($reflectionType instanceof \ReflectionNamedType && $reflectionType->allowsNull()),
            'min' => $min,
            'max' => $max,
        ];
    }
}
