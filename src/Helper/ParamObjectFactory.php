<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Helper;

use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\JsonRPC\Core\Contracts\ParamObjectFactoryInterface;
use Tourze\JsonRPC\Core\Contracts\RpcParamInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;

/**
 * 参数对象工厂：将原始请求数据转换为参数对象实例
 *
 * 处理流程：
 * 1. 将数组数据 JSON 编码
 * 2. 使用 Symfony Serializer 反序列化为参数对象
 * 3. 使用 Symfony Validator 验证参数对象
 * 4. 返回参数对象或抛出异常
 */
final readonly class ParamObjectFactory implements ParamObjectFactoryInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
    }

    public function create(string $className, array $data): RpcParamInterface
    {
        if (!$this->supports($className)) {
            throw new ApiException(sprintf('参数类 %s 必须实现 %s 接口', $className, RpcParamInterface::class));
        }

        $param = $this->deserialize($className, $data);
        $this->validate($param);

        return $param;
    }

    public function supports(string $className): bool
    {
        return is_a($className, RpcParamInterface::class, true);
    }

    /**
     * 反序列化数据为参数对象
     *
     * @template T of RpcParamInterface
     *
     * @param class-string<T>      $className
     * @param array<string, mixed> $data
     *
     * @return T
     *
     * @throws ApiException
     */
    private function deserialize(string $className, array $data): RpcParamInterface
    {
        try {
            $json = json_encode($data, \JSON_THROW_ON_ERROR);

            /** @var T $param */
            $param = $this->serializer->deserialize($json, $className, 'json');

            return $param;
        } catch (\JsonException $e) {
            throw new ApiException(sprintf('参数序列化失败：%s', $e->getMessage()), previous: $e);
        } catch (SerializerExceptionInterface $e) {
            throw new ApiException(sprintf('参数反序列化失败：%s', $e->getMessage()), previous: $e);
        }
    }

    /**
     * 验证参数对象
     *
     * @throws ApiException
     */
    private function validate(RpcParamInterface $param): void
    {
        $violations = $this->validator->validate($param);

        if (count($violations) > 0) {
            throw new ApiException($this->formatViolations($violations));
        }
    }

    /**
     * 格式化验证错误消息
     *
     * @param ConstraintViolationListInterface<ConstraintViolationInterface> $violations
     */
    private function formatViolations(ConstraintViolationListInterface $violations): string
    {
        /** @var ConstraintViolationInterface $first */
        $first = $violations->get(0);
        $propertyPath = $first->getPropertyPath();
        $message = $first->getMessage();

        if ('' !== $propertyPath) {
            return sprintf('参数%s校验不通过：%s', $propertyPath, $message);
        }

        return sprintf('参数校验不通过：%s', $message);
    }
}
