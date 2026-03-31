<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Support;

use DateTimeImmutable;
use MasyaSmv\FinamSdk\Exceptions\ResponseMappingException;

/**
 * @phpstan-type ApiScalar null|bool|int|float|string
 * @phpstan-type ApiNestedArray array<int|string, ApiScalar|array<int|string, ApiScalar|array<int|string, ApiScalar>>>
 * @phpstan-type ApiMap array<int|string, ApiScalar|ApiNestedArray>
 * @phpstan-type ApiResponse array<string, ApiScalar|ApiNestedArray>
 * @phpstan-type HeaderMap array<string, list<string>>
 * @phpstan-type RequestContext array<string, scalar|array<int|string, scalar|null>|null>
 * @psalm-type ApiScalar = null|bool|int|float|string
 * @psalm-type ApiNestedArray = array<int|string, ApiScalar|array<int|string, ApiScalar|array<int|string, ApiScalar>>>
 * @psalm-type ApiMap = array<int|string, ApiScalar|ApiNestedArray>
 * @psalm-type ApiResponse = array<string, ApiScalar|ApiNestedArray>
 * @psalm-type HeaderMap = array<string, list<string>>
 * @psalm-type RequestContext = array<string, scalar|array<int|string, scalar|null>|null>
 */
final class ApiValueReader
{
    /**
     * @param ApiMap $data
     *
     * @return ApiMap
     */
    public function requireArray(array $data, string $field): array
    {
        $value = $data[$field] ?? null;

        if (!is_array($value)) {
            throw new ResponseMappingException(sprintf('Field "%s" must be an object.', $field));
        }

        return $value;
    }

    /**
     * @param ApiMap $data
     */
    public function requireString(array $data, string $field): string
    {
        $value = $data[$field] ?? null;

        if (!is_string($value)) {
            throw new ResponseMappingException(sprintf('Field "%s" must be a string.', $field));
        }

        return $value;
    }

    /**
     * @param ApiMap $data
     */
    public function optionalString(array $data, string $field): ?string
    {
        $value = $data[$field] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param ApiMap $data
     */
    public function requireBool(array $data, string $field): bool
    {
        $value = $data[$field] ?? null;

        if (!is_bool($value)) {
            throw new ResponseMappingException(sprintf('Field "%s" must be a boolean.', $field));
        }

        return $value;
    }

    /**
     * @param ApiMap $data
     */
    public function requireIntLike(array $data, string $field, string $context): int
    {
        $value = $data[$field] ?? null;

        if (!is_int($value) && !is_string($value)) {
            throw new ResponseMappingException(
                sprintf('Field "%s" in "%s" must be an integer or integer-like string.', $field, $context),
            );
        }

        return (int) $value;
    }

    /**
     * @param ApiMap $data
     */
    public function optionalInt(array $data, string $field): ?int
    {
        $value = $data[$field] ?? null;

        return is_int($value) ? $value : null;
    }

    public function parseDateTime(string $value, string $field): DateTimeImmutable
    {
        try {
            return new DateTimeImmutable($value);
        } catch (\Exception $exception) {
            throw new ResponseMappingException(
                sprintf('Field "%s" contains invalid datetime value "%s".', $field, $value),
                previous: $exception,
            );
        }
    }

    /**
     * @param ApiMap $data
     */
    public function optionalDateTime(array $data, string $field): ?DateTimeImmutable
    {
        $value = $this->optionalString($data, $field);

        if ($value === null || $value === '') {
            return null;
        }

        return $this->parseDateTime($value, $field);
    }

    /**
     * @param ApiMap $data
     */
    public function extractDecimalValue(array $data, string $field): string
    {
        return $this->requireString($data, 'value');
    }

    /**
     * @param ApiScalar|ApiNestedArray $value
     */
    public function extractOptionalDecimalValue($value, string $field): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (!is_array($value)) {
            throw new ResponseMappingException(sprintf('Field "%s" must be a decimal object or string.', $field));
        }

        return $this->requireString($value, 'value');
    }

    /**
     * @param ApiScalar|ApiNestedArray $value
     *
     * @return list<string>
     */
    public function stringList($value, string $field): array
    {
        if (!is_array($value)) {
            throw new ResponseMappingException(sprintf('Field "%s" must be a list of strings.', $field));
        }

        $items = [];

        foreach ($value as $item) {
            if (!is_string($item)) {
                throw new ResponseMappingException(sprintf('Field "%s" must contain only strings.', $field));
            }

            $items[] = $item;
        }

        /** @var list<string> $items */
        return $items;
    }

    /**
     * @param ApiScalar|ApiNestedArray $value
     *
     * @return list<array<int|string, ApiScalar|ApiNestedArray>>
     */
    public function listOfArrays($value, string $field): array
    {
        if (!is_array($value)) {
            throw new ResponseMappingException(sprintf('Field "%s" must be a list of objects.', $field));
        }

        $items = [];

        foreach ($value as $item) {
            if (!is_array($item)) {
                throw new ResponseMappingException(sprintf('Field "%s" must contain only objects.', $field));
            }

            $items[] = $item;
        }

        /** @var list<array<int|string, ApiScalar|ApiNestedArray>> $items */
        return $items;
    }

    /**
     * @param ApiScalar|ApiNestedArray $value
     *
     * @return HeaderMap
     */
    public function headerMap($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $headers = [];

        foreach ($value as $name => $headerValues) {
            if (!is_string($name) || !is_array($headerValues)) {
                continue;
            }

            $values = [];

            foreach ($headerValues as $headerValue) {
                if (is_string($headerValue)) {
                    $values[] = $headerValue;
                }
            }

            $headers[$name] = $values;
        }

        return $headers;
    }

    /**
     * @param ApiResponse $response
     *
     * @return RequestContext
     */
    public function requestContext(array $response): array
    {
        $meta = $response['meta'] ?? null;

        if (!is_array($meta)) {
            return [];
        }

        $request = $meta['request'] ?? null;

        if (!is_array($request)) {
            return [];
        }

        $context = [];

        foreach ($request as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $context[$key] = $value;
                continue;
            }

            if (!is_array($value)) {
                continue;
            }

            $nested = [];

            foreach ($value as $nestedKey => $nestedValue) {
                if ((is_int($nestedKey) || is_string($nestedKey)) && (is_scalar($nestedValue) || $nestedValue === null)) {
                    $nested[$nestedKey] = $nestedValue;
                }
            }

            $context[$key] = $nested;
        }

        return $context;
    }

    /**
     * @param ApiMap $payload
     * @param list<string> $keys
     */
    public function firstStringByKeys(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $payload[$key] ?? null;

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param HeaderMap $headers
     * @param list<string> $names
     */
    public function firstHeaderValueByNames(array $headers, array $names): ?string
    {
        foreach ($names as $name) {
            foreach ($headers as $headerName => $values) {
                if (mb_strtolower($headerName) !== $name) {
                    continue;
                }

                foreach ($values as $value) {
                    if ($value !== '') {
                        return $value;
                    }
                }
            }
        }

        return null;
    }
}
