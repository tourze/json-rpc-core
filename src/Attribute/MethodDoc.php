<?php

namespace Tourze\JsonRPC\Core\Attribute;

/**
 * 标记方法文档，如果没使用这个来标记，默认就会读取类的注释
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class MethodDoc
{
    public function __construct(public string $summary = '', public string $description = '')
    {
    }
}
