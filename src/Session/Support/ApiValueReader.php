<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Support;

use DateTimeImmutable;
use MasyaSmv\FinamSdk\Collections\StringCollection;
use MasyaSmv\FinamSdk\Collections\Transport\ApiPayloadCollection;
use MasyaSmv\FinamSdk\Dto\Transport\ApiHeaders;
use MasyaSmv\FinamSdk\Dto\Transport\ApiRequestContext;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;
use MasyaSmv\FinamSdk\Exceptions\ResponseMappingException;

/**
 * @phpstan-import-type ApiNode from ApiPayload
 * @psalm-import-type ApiNode from ApiPayload
 */
final class ApiValueReader
{
    public function requireObject(ApiPayload $data, string $field): ApiPayload
    {
        $value = $data->object($field);

        if ($value === null) {
            throw new ResponseMappingException(sprintf('Field "%s" must be an object.', $field));
        }

        return $value;
    }

    public function optionalObject(ApiPayload $data, string $field): ?ApiPayload
    {
        return $data->object($field);
    }

    public function requireString(ApiPayload $data, string $field): string
    {
        $value = $data->string($field);

        if ($value === null) {
            throw new ResponseMappingException(sprintf('Field "%s" must be a string.', $field));
        }

        return $value;
    }

    public function optionalString(ApiPayload $data, string $field): ?string
    {
        return $data->string($field);
    }

    public function requireBool(ApiPayload $data, string $field): bool
    {
        $value = $data->bool($field);

        if ($value === null) {
            throw new ResponseMappingException(sprintf('Field "%s" must be a boolean.', $field));
        }

        return $value;
    }

    public function requireInt(ApiPayload $data, string $field, string $context): int
    {
        $value = $data->int($field);

        if ($value === null) {
            throw new ResponseMappingException(
                sprintf('Field "%s" in "%s" must be an integer or numeric string.', $field, $context),
            );
        }

        return $value;
    }

    public function optionalInt(ApiPayload $data, string $field): ?int
    {
        return $data->int($field);
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

    public function optionalDateTime(ApiPayload $data, string $field): ?DateTimeImmutable
    {
        $value = $this->optionalString($data, $field);

        if ($value === null || $value === '') {
            return null;
        }

        return $this->parseDateTime($value, $field);
    }

    public function requireDecimal(ApiPayload $data, string $field): string
    {
        $value = $data->decimalString($field);

        if ($value === null) {
            throw new ResponseMappingException(sprintf('Field "%s" must be a decimal object or string.', $field));
        }

        return $value;
    }

    public function optionalDecimal(ApiPayload $data, string $field): ?string
    {
        return $data->decimalString($field);
    }

    public function requireStringList(ApiPayload $data, string $field): StringCollection
    {
        $items = $data->stringList($field);

        if ($items === null) {
            throw new ResponseMappingException(sprintf('Field "%s" must be a list of strings.', $field));
        }

        return $items;
    }

    public function requireObjectList(ApiPayload $data, string $field): ApiPayloadCollection
    {
        $items = $data->objectList($field);

        if ($items === null) {
            throw new ResponseMappingException(sprintf('Field "%s" must be a list of objects.', $field));
        }

        return $items;
    }

    public function requestContext(ApiResponse $response): ?ApiRequestContext
    {
        return $response->meta()->request();
    }

    /**
     * @param list<string> $keys
     */
    public function firstStringByKeys(ApiPayload $payload, array $keys): ?string
    {
        return $payload->firstStringByKeys(...$keys);
    }

    /**
     * @param list<string> $names
     */
    public function firstHeaderValueByNames(ApiHeaders $headers, array $names): ?string
    {
        return $headers->firstValueByNames(...$names);
    }
}
