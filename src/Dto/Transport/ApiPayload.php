<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Transport;

use MasyaSmv\FinamSdk\Collections\StringCollection;
use MasyaSmv\FinamSdk\Collections\Transport\ApiPayloadCollection;

/**
 * @phpstan-type ApiPrimitive null|bool|int|float|string
 * @phpstan-type ApiNode ApiPrimitive|array<int|string, ApiPrimitive|array<int|string, ApiPrimitive|array<int|string, ApiPrimitive|null>|null>|null>
 * @psalm-type ApiPrimitive = null|bool|int|float|string
 * @psalm-type ApiNode = ApiPrimitive|array<int|string, ApiPrimitive|array<int|string, ApiPrimitive|array<int|string, ApiPrimitive|null>|null>|null>
 */
final class ApiPayload
{
    /**
     * @param array<string, ApiNode> $values
     */
    public function __construct(private array $values = [])
    {
    }

    public function has(string $field): bool
    {
        return array_key_exists($field, $this->values);
    }

    public function string(string $field): ?string
    {
        $value = $this->values[$field] ?? null;

        return is_string($value) ? $value : null;
    }

    public function bool(string $field): ?bool
    {
        $value = $this->values[$field] ?? null;

        return is_bool($value) ? $value : null;
    }

    public function int(string $field): ?int
    {
        $value = $this->values[$field] ?? null;

        if (is_int($value)) {
            return $value;
        }

        if (!is_string($value) || preg_match('/^-?\d+$/', $value) !== 1) {
            return null;
        }

        return (int) $value;
    }

    public function object(string $field): ?self
    {
        $value = $this->values[$field] ?? null;

        if (!is_array($value) || self::isList($value)) {
            return null;
        }

        /** @var array<string, ApiNode> $value */
        return new self($value);
    }

    public function objectList(string $field): ?ApiPayloadCollection
    {
        $value = $this->values[$field] ?? null;

        if (!is_array($value) || !self::isList($value)) {
            return null;
        }

        $items = [];

        foreach ($value as $item) {
            if (!is_array($item) || self::isList($item)) {
                return null;
            }

            /** @var array<string, ApiNode> $item */
            $items[] = new self($item);
        }

        return new ApiPayloadCollection($items);
    }

    public function stringList(string $field): ?StringCollection
    {
        $value = $this->values[$field] ?? null;

        if (!is_array($value) || !self::isList($value)) {
            return null;
        }

        $items = [];

        foreach ($value as $item) {
            if (!is_string($item)) {
                return null;
            }

            $items[] = $item;
        }

        return new StringCollection($items);
    }

    public function decimalString(string $field): ?string
    {
        $scalar = $this->string($field);

        if ($scalar !== null) {
            return $scalar;
        }

        return $this->object($field)?->string('value');
    }

    /**
     * @return array<string, ApiNode>
     */
    public function toArray(): array
    {
        return $this->values;
    }

    public function firstStringByKeys(string ...$keys): ?string
    {
        foreach ($keys as $key) {
            $value = $this->string($key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param array<int|string, ApiNode> $value
     */
    private static function isList(array $value): bool
    {
        $index = 0;

        foreach ($value as $key => $_) {
            if ($key !== $index) {
                return false;
            }

            $index++;
        }

        return true;
    }
}
