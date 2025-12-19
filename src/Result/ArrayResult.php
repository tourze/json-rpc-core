<?php

declare(strict_types=1);

namespace Tourze\JsonRPC\Core\Result;

use Tourze\JsonRPC\Core\Contracts\RpcResultInterface;

/**
 * 数组结果包装类
 *
 * 用于将现有返回 array 的 Procedure 迁移到 RpcResultInterface
 * 这是一个过渡方案，建议后续逐步迁移到具体的 Result 类
 *
 * @implements \ArrayAccess<string, mixed>
 * @implements \IteratorAggregate<string, mixed>
 */
readonly class ArrayResult implements RpcResultInterface, \ArrayAccess, \IteratorAggregate, \Countable, \JsonSerializable
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public array $data = [],
    ) {
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        // readonly class, do nothing
    }

    public function offsetUnset(mixed $offset): void
    {
        // readonly class, do nothing
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->data);
    }

    public function count(): int
    {
        return count($this->data);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public static function getMockResult(): ?self
    {
        return new self([
            'example_key' => 'example_value',
        ]);
    }
}
