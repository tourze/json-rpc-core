<?php

namespace Tourze\JsonRPC\Core\Procedure;

use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\Exception\InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceMethodsSubscriberTrait;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithResultDocInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithValidatedParamsInterface;
use Tourze\JsonRPC\Core\Event\AfterMethodApplyEvent;
use Tourze\JsonRPC\Core\Event\BeforeMethodApplyEvent;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * JsonRpcMethodInterface实现起来太别扭了
 * 远不如原来我们设计的用法
 */
abstract class BaseProcedure implements JsonRpcMethodInterface, MethodWithValidatedParamsInterface, MethodWithResultDocInterface, ServiceSubscriberInterface
{
    use ServiceMethodsSubscriberTrait;

    #[SubscribedService]
    private function getBaseProcedureLogger(): LoggerInterface
    {
        return $this->container->get(__METHOD__);
    }

    private static PropertyAccessor $propertyAccessor;

    private function getPropertyAccessor(): PropertyAccessor
    {
        return static::$propertyAccessor ??= PropertyAccess::createPropertyAccessor();
    }

    #[SubscribedService]
    private function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->container->get(__METHOD__);
    }

    #[SubscribedService]
    private function getValidator(): ValidatorInterface
    {
        return $this->container->get(__METHOD__);
    }

    /**
     * @var array|null 原始属性列表
     */
    public ?array $paramList = null;

    /**
     * 设置和计算参数
     *
     * @throws ApiException
     * @throws \Throwable
     */
    public function assignParams(?array $paramList = null): void
    {
        $this->paramList = null;
        if ($paramList) {
            foreach ($paramList as $k => $v) {
                // TODO 在这里判断一下，成员是不是一个Entity，如果是的话，尝试读取并写入
                if ($this->getPropertyAccessor()->isWritable($this, $k)) {
                    try {
                        $this->getPropertyAccessor()->setValue($this, $k, $v);
                    } catch (InvalidArgumentException $e) {
                        throw new ApiException("参数{$k}不合法", 0, previous: $e);
                    }
                }
            }

            $this->paramList = $paramList;
        }

        // 校验数据
        foreach ($this->getParamsConstraint()->fields as $field => $rules) {
            try {
                $v = $this->{$field};
            } catch (\Throwable $e) {
                $this->getBaseProcedureLogger()->warning('读取参数时报错', [
                    'procedure' => get_class($this),
                    'field' => $field,
                    'exception' => $e,
                ]);
                if (str_contains($e->getMessage(), 'must not be accessed before initialization')) {
                    throw new ApiException("参数{$field}不能为空");
                }

                throw $e;
            }

            foreach ($rules as $rule) {
                if (!($rule instanceof Constraint)) {
                    continue;
                }
                $errors = $this->getValidator()->validate($v, $rule);
                if ((is_countable($errors) ? count($errors) : 0) > 0) {
                    $error = $errors->get(0);
                    throw new ApiException("参数{$field}校验不通过：{$error}");
                }
            }
        }
    }

    public function __invoke(JsonRpcRequest $request): mixed
    {
        // 执行前触发
        $beforeEvent = new BeforeMethodApplyEvent();
        $beforeEvent->setMethod($this);
        $beforeEvent->setRequest($request);
        $beforeEvent->setName($request->getMethod());
        $beforeEvent->setParams($request->getParams());
        $this->getEventDispatcher()->dispatch($beforeEvent);
        if (null !== $beforeEvent->getResult()) {
            $this->getBaseProcedureLogger()->debug('执行前直接返回结果', $beforeEvent->getResult());

            return $beforeEvent->getResult();
        }

        $this->assignParams($beforeEvent->getParams()->toArray());
        $res = $this->execute();

        // 执行后触发
        $afterEvent = new AfterMethodApplyEvent();
        $afterEvent->setMethod($this);
        $afterEvent->setRequest($request);
        $afterEvent->setName($request->getMethod());
        $afterEvent->setParams($beforeEvent->getParams());
        $afterEvent->setResult($res);
        $this->getEventDispatcher()->dispatch($afterEvent);

        return $afterEvent->getResult();
    }

    /**
     * 根据变量类型生成规则
     */
    protected static function genTypeValidatorByReflectionType(\ReflectionType $type): Type|AtLeastOneOf|null
    {
        // 联合类型，如果满足一个就给过
        if ($type instanceof \ReflectionUnionType) {
            $AtLeastOneOf = [];
            foreach ($type->getTypes() as $subType) {
                $tmp = static::genTypeValidatorByReflectionType($subType);
                if ($tmp) {
                    $AtLeastOneOf[] = $tmp;
                }
            }

            return new AtLeastOneOf($AtLeastOneOf);
        }

        // 只有内建类型，我们才支持输入
        if (!$type->isBuiltin()) {
            return null;
        }

        return static::genTypeValidatorByTypeName($type->getName());
    }

    protected static function genTypeValidatorByTypeName(string $typeName): Type|null
    {
        if ('null' === $typeName) {
            return new Type('null');
        }

        if ('string' === $typeName) {
            return new Type('string');
        }

        if ('bool' === $typeName || 'boolean' === $typeName) {
            return new Type('bool');
        }

        if ('float' === $typeName || 'double' === $typeName) {
            return new Type('float');
        }

        // 因为前端传入的参数，int也可能传入字符串的，所以下面这个校验不太好处理，只能暂时注释了
        if ('int' === $typeName || 'integer' === $typeName) {
            return new Type('integer');
        }

        if ('array' === $typeName) {
            return new Type('array');
        }

        // TODO 对应类似 public ?int $page 这种入参，我们要怎么处理？
        return null;
    }

    /**
     * 因为目前前端入参还不够严谨，所以实际上会存在以下情况：
     * 1. 期望传入 1，实际传入的是 '1'；
     * 2. 期望传入 '1.0'，实际传入的是 1.0；
     * 但是我们又不能要求前端马上全部改完，所以只能在这里我们兼容一次
     */
    protected static function makeTypeCompatible(Type|AtLeastOneOf $type): AtLeastOneOf|Type
    {
        if ($type instanceof Type) {
            if (in_array($type->type, ['int', 'integer', 'string', 'float', 'double'])) {
                return new AtLeastOneOf([
                    new Type('integer'),
                    new Type('string'),
                    new Type('float'),
                ]);
            }

            if (in_array($type->type, ['boolean', 'bool'])) {
                return new AtLeastOneOf([
                    new Type('string'),
                    new Type('integer'),
                    new Type('bool'),
                ]);
            }
        }

        return $type;
    }

    /**
     * 根据当前属性的定义，自动生成规则
     */
    public function getParamsConstraint(): Collection
    {
        // 根据属性中的定义，自动生成
        $fields = [];
        foreach ((new \ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            // 过滤特殊的参数
            if (in_array($property->getName(), ['paramList', '_class'])) {
                continue;
            }

            $tmp = $this->getPropertyConstraint($property);
            if ($tmp) {
                $fields[$property->getName()] = $tmp;
            }
        }

        return new Collection($fields, allowExtraFields: true, allowMissingFields: true);
    }

    protected function getPropertyConstraint(\ReflectionProperty $property): Constraint|Collection|array|null
    {
        // 如果是枚举类型，那我们取枚举的实际类型
        $type = $property->getType();
        if ($type instanceof \ReflectionNamedType) {
            $name = $type->getName();
            if (class_exists($name) && is_subclass_of($name, \BackedEnum::class)) {
                $reflectionEnum = new \ReflectionEnum($name);
                return static::genTypeValidatorByTypeName($reflectionEnum->getBackingType()->getName());
            }
        }

        $all = [];

        // 基础类型，基础处理啦
        if ($property->getType()) {
            if ($tmp = static::genTypeValidatorByReflectionType($property->getType())) {
                $all[] = static::makeTypeCompatible($tmp);
            }
        }

        // 如果在成员那里有定义Asset规则，那就直接加入
        if ($property->getType() && method_exists($property->getType(), 'isBuiltin') && $property->getType()->isBuiltin()) {
            foreach ($property->getAttributes() as $attribute) {
                if (is_subclass_of($attribute->getName(), Constraint::class)) {
                    $all[] = $attribute->newInstance();
                }
            }
        }

        if (!empty($all)) {
            if (1 === count($all)) {
                return array_shift($all);
            } else {
                // TODO 多个规则的话，会有问题，报一个类型不匹配的校验错误，暂时没办法解决，先跳过，只使用基础类型
                // $fields[$property->getName()] = new All($all);
                if ($tmp = static::genTypeValidatorByReflectionType($property->getType())) {
                    return static::makeTypeCompatible($tmp);
                }
            }
        }
        return null;
    }

    /**
     * 获取指定参数的文档描述
     */
    public function getPropertyDocument(string $propertyName): ?array
    {
        $property = (new \ReflectionClass(static::class))->getProperty($propertyName);

        $MethodParam = $property->getAttributes(MethodParam::class);
        if (empty($MethodParam)) {
            return null;
        }

        /** @var MethodParam $MethodParam */
        $MethodParam = $MethodParam[0]->newInstance();

        $type = method_exists($property->getType(), 'getName')
            ? $property->getType()->getName()
            : 'mixed';

        $min = $max = null;
        foreach ($property->getAttributes() as $attribute) {
            if (!is_subclass_of($attribute->getName(), Constraint::class)) {
                continue;
            }

            if (Range::class === $attribute->getName()) {
                /** @var Range $instance */
                $instance = $attribute->newInstance();
                $min = $instance->min;
                $max = $instance->max;
            }
        }

        return [
            'name' => $property->getName(),
            'type' => $type,
            'description' => $MethodParam->description,
            'default' => $property->getDefaultValue(),
            'nullable' => $property->hasDefaultValue(),
            'min' => $min,
            'max' => $max,
        ];
    }

    /**
     * 返回Mock数据
     */
    public static function getMockResult(): ?array
    {
        return null;
    }
}
