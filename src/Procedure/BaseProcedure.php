<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Procedure;

use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
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
use Tourze\JsonRPC\Core\Domain\JsonRpcMethodInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithResultDocInterface;
use Tourze\JsonRPC\Core\Domain\MethodWithValidatedParamsInterface;
use Tourze\JsonRPC\Core\Event\AfterMethodApplyEvent;
use Tourze\JsonRPC\Core\Event\BeforeMethodApplyEvent;
use Tourze\JsonRPC\Core\Helper\ParameterProcessor;
use Tourze\JsonRPC\Core\Helper\PropertyConstraintExtractor;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;

/**
 * JsonRpcMethodInterface实现起来太别扭了
 * 远不如原来我们设计的用法.
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

    private static ?PropertyAccessor $propertyAccessor = null;

    private function getPropertyAccessor(): PropertyAccessor
    {
        return self::$propertyAccessor ??= PropertyAccess::createPropertyAccessor();
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

    private function getParameterProcessor(): ParameterProcessor
    {
        return new ParameterProcessor(
            $this->getPropertyAccessor(),
            $this->getValidator(),
            $this->getBaseProcedureLogger()
        );
    }

    /**
     * @var array<string, mixed>|null 原始属性列表
     */
    public ?array $paramList = null;

    /**
     * 设置和计算参数.
     *
     * @param array<string, mixed>|null $paramList
     */
    public function assignParams(?array $paramList = null): void
    {
        $this->paramList = $paramList;
        $this->getParameterProcessor()->assignParameters($this, $paramList);
    }

    public function __invoke(JsonRpcRequest $request): mixed
    {
        // 执行前触发
        $beforeEvent = new BeforeMethodApplyEvent();
        $beforeEvent->setMethod($this);
        $beforeEvent->setRequest($request);
        $beforeEvent->setName($request->getMethod());
        $beforeEvent->setParams($request->getParams() ?? new JsonRpcParams());
        $this->getEventDispatcher()->dispatch($beforeEvent);
        if (null !== $beforeEvent->getResult()) {
            $this->getBaseProcedureLogger()->debug('执行前直接返回结果', ['result' => $beforeEvent->getResult()]);

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
     * 根据当前属性的定义，自动生成规则.
     */
    public function getParamsConstraint(): Collection
    {
        $fields = [];
        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (in_array($property->getName(), ['paramList', '_class'], true)) {
                continue;
            }

            $constraint = PropertyConstraintExtractor::extractConstraint($property);
            if (null !== $constraint) {
                $fields[$property->getName()] = $constraint;
            }
        }

        return new Collection($fields, allowExtraFields: true, allowMissingFields: true);
    }

    /**
     * 获取指定参数的文档描述.
     *
     * @return array<string, mixed>|null
     */
    public function getPropertyDocument(string $propertyName): ?array
    {
        $property = (new \ReflectionClass($this))->getProperty($propertyName);

        $MethodParam = $property->getAttributes(MethodParam::class);
        if ([] === $MethodParam) {
            return null;
        }

        $MethodParam = $MethodParam[0]->newInstance();
        assert($MethodParam instanceof MethodParam);

        $reflectionType = $property->getType();
        $type = ($reflectionType instanceof \ReflectionNamedType)
            ? $reflectionType->getName()
            : 'mixed';

        $min = $max = null;
        foreach ($property->getAttributes() as $attribute) {
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
     * 返回Mock数据.
     *
     * @return array<string, mixed>|null
     */
    public static function getMockResult(): ?array
    {
        return null;
    }
}
