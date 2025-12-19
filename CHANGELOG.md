# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Breaking Changes

- **Procedure Return Type Model**: All Procedures must now return objects implementing `RpcResultInterface`
  - The `__invoke()` method signature changed from `__invoke(): mixed` to `__invoke(): RpcResultInterface`
  - The `execute()` method signature changed from `execute(RpcParamInterface $param): mixed` to `execute(RpcParamInterface $param): RpcResultInterface`
  - Framework layer handles serialization via `ResultObjectSerializer`

- **Procedure Parameter Model**: All Procedures must now use parameter objects implementing `RpcParamInterface`
  - Removed `BaseProcedure::$paramList` property
  - Removed `BaseProcedure::assignParams()` method
  - The `execute()` method signature changed from `execute(): mixed` to `execute(RpcParamInterface $param): mixed`
  - All 164 Procedures migrated to the new parameter object pattern
  - 238 parameter classes created across all bundles

### Added

- **RpcResultInterface**: Marker interface for result value objects
  - Location: `Tourze\JsonRPC\Core\Contracts\RpcResultInterface`
  - Enforces type-safe return structures for all Procedures

- **ResultProperty attribute**: Documentation attribute for result properties
  - Location: `Tourze\JsonRPC\Core\Attribute\ResultProperty`
  - Supports: description, nullable flag

- **EmptyResult**: Generic empty result class (serializes to `{}`)
  - Location: `Tourze\JsonRPC\Core\Result\EmptyResult`

- **SuccessResult**: Generic success/failure result class
  - Location: `Tourze\JsonRPC\Core\Result\SuccessResult`
  - Properties: `success` (bool), `message` (?string)

- **ResultObjectSerializer**: Framework-layer serialization helper
  - Location: `Tourze\JsonRPC\Core\Helper\ResultObjectSerializer`
  - Handles: nested Results, arrays, DateTime, Enums
  - Includes: circular reference detection, depth limiting

- **RpcParamInterface**: Marker interface for parameter value objects
  - Location: `Tourze\JsonRPC\Core\Contracts\RpcParamInterface`

- **ParamObjectFactory**: Factory for deserializing JSON to parameter objects
  - Location: `Tourze\JsonRPC\Core\Helper\ParamObjectFactory`
  - Supports Symfony Serializer for deserialization
  - Supports Symfony Validator for validation
  - Supports complex types: nested objects, arrays, enums

- **ParamDocExtractor**: Utility for extracting documentation from parameter classes
  - Location: `Tourze\JsonRPC\Core\Helper\ParamDocExtractor`
  - Extracts: name, type, description, required status, default value, constraints

- **MethodParam attribute**: Extended to support `TARGET_PARAMETER` in addition to `TARGET_PROPERTY`
  - Allows use on constructor parameters with PHP 8+ constructor promotion

### Changed

- **BaseProcedure**: Now automatically detects and injects parameter objects
  - Detects parameter class from `execute()` method signature
  - Uses `ParamObjectFactory` for deserialization and validation
  - Provides `createParamObject()` and `detectParamClass()` protected methods

### Migration Guide

1. Create a parameter class for each Procedure:

```php
readonly class YourProcedureParam implements RpcParamInterface
{
    public function __construct(
        #[MethodParam(description: 'User ID')]
        #[Assert\NotBlank]
        public string $userId,
    ) {}
}
```

2. Update your Procedure's execute method:

```php
// Before
public function execute(): array
{
    return $this->doSomething($this->userId);
}

// After
public function execute(RpcParamInterface $param): array
{
    assert($param instanceof YourProcedureParam);
    return $this->doSomething($param->userId);
}
```

3. Remove old property declarations from the Procedure class.

See `/specs/001-rpc-param-valueobject/quickstart.md` for detailed examples.
