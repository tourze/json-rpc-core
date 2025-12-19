<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Helper;

use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\Exception\InvalidArgumentException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;

readonly class ParameterProcessor
{
    public function __construct(
        private PropertyAccessor $propertyAccessor,
        private ValidatorInterface $validator,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed>|null $paramList
     */
    public function assignParameters(object $target, ?array $paramList): void
    {
        if (null === $paramList || [] === $paramList) {
            return;
        }

        $this->setProperties($target, $paramList);
        $this->validateParameters($target);
    }

    /**
     * @param array<string, mixed> $paramList
     */
    private function setProperties(object $target, array $paramList): void
    {
        foreach ($paramList as $key => $value) {
            if ($this->propertyAccessor->isWritable($target, $key)) {
                try {
                    $this->propertyAccessor->setValue($target, $key, $value);
                } catch (InvalidArgumentException $e) {
                    throw new ApiException("参数{$key}不合法", 0, previous: $e);
                }
            }
        }
    }

    private function validateParameters(object $target): void
    {
        $constraint = $this->getParametersConstraint($target);

        foreach ($constraint->fields as $field => $rules) {
            $value = $this->getParameterValue($target, $field);
            $this->validateParameterValue($value, $rules, $field);
        }
    }

    private function getParameterValue(object $target, string $field): mixed
    {
        try {
            return $this->propertyAccessor->getValue($target, $field);
        } catch (\Throwable $e) {
            $this->logger->warning('读取参数时报错', [
                'procedure' => get_class($target),
                'field' => $field,
                'exception' => $e,
            ]);

            if (str_contains($e->getMessage(), 'must not be accessed before initialization')) {
                throw new ApiException("参数{$field}不能为空");
            }

            throw $e;
        }
    }

    private function validateParameterValue(mixed $value, mixed $rules, string $field): void
    {
        // 如果 rules 是单个 Constraint，转换为数组
        if ($rules instanceof Constraint) {
            $rules = [$rules];
        }

        // 如果不是数组，跳过验证
        if (!is_array($rules)) {
            return;
        }

        foreach ($rules as $rule) {
            if (!($rule instanceof Constraint)) {
                continue;
            }

            $errors = $this->validator->validate($value, $rule);
            if (count($errors) > 0) {
                $error = $errors->get(0);
                throw new ApiException("参数{$field}校验不通过：" . $error->getMessage());
            }
        }
    }

    private function getParametersConstraint(object $target): Collection
    {
        $fields = [];
        $reflection = new \ReflectionClass($target);

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
}
