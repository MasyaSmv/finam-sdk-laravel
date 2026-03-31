<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session;

use DateTimeImmutable;
use DateTimeInterface;
use MasyaSmv\FinamSdk\Collections\OperationCollection;
use MasyaSmv\FinamSdk\Collections\OrderCollection;
use MasyaSmv\FinamSdk\Contracts\Api\AccountApiInterface;
use MasyaSmv\FinamSdk\Contracts\Api\ConnectApiInterface;
use MasyaSmv\FinamSdk\Contracts\Api\OrderApiInterface;
use MasyaSmv\FinamSdk\Contracts\FinamSessionInterface;
use MasyaSmv\FinamSdk\Dto\Account\OperationDto;
use MasyaSmv\FinamSdk\Dto\Account\OperationTradeDto;
use MasyaSmv\FinamSdk\Dto\Account\TransactionsRequest;
use MasyaSmv\FinamSdk\Dto\Connect\SessionDetailsDto;
use MasyaSmv\FinamSdk\Dto\Order\OrderDto;
use MasyaSmv\FinamSdk\Dto\Order\OrderRequest;
use MasyaSmv\FinamSdk\Dto\Order\OrdersRequest;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderInputDto;
use MasyaSmv\FinamSdk\Dto\Order\PlaceOrderRequest;
use MasyaSmv\FinamSdk\Dto\Shared\Interval;
use MasyaSmv\FinamSdk\Dto\Shared\MoneyDto;
use MasyaSmv\FinamSdk\Exceptions\AccountResolutionException;
use MasyaSmv\FinamSdk\Exceptions\ApiHttpException;
use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;
use MasyaSmv\FinamSdk\Exceptions\InvalidResponseException;
use MasyaSmv\FinamSdk\Exceptions\ResponseMappingException;

/**
 * @phpstan-type ApiScalar null|bool|int|float|string
 * @phpstan-type ApiNestedArray array<int|string, ApiScalar|array<int|string, ApiScalar>>
 * @phpstan-type ApiMap array<int|string, ApiScalar|ApiNestedArray>
 * @phpstan-type ApiResponse array<string, ApiScalar|ApiNestedArray>
 * @phpstan-type HeaderMap array<string, list<string>>
 * @psalm-type ApiScalar = null|bool|int|float|string
 * @psalm-type ApiNestedArray = array<int|string, ApiScalar|array<int|string, ApiScalar>>
 * @psalm-type ApiMap = array<int|string, ApiScalar|ApiNestedArray>
 * @psalm-type ApiResponse = array<string, ApiScalar|ApiNestedArray>
 * @psalm-type HeaderMap = array<string, list<string>>
 */
final class FinamSession implements FinamSessionInterface
{
    public function __construct(
        private ConnectApiInterface $connectApi,
        private AccountApiInterface $accountApi,
        private OrderApiInterface $orderApi,
    ) {
    }

    public function sessionDetails(): SessionDetailsDto
    {
        /** @var ApiResponse $response */
        $response = $this->connectApi->tokenDetails();

        $data = $this->extractResponseData(
            $response,
            'sessions/details',
        );

        return new SessionDetailsDto(
            createdAt: $this->parseDateTime($this->requireString($data, 'created_at'), 'created_at'),
            expiresAt: $this->parseDateTime($this->requireString($data, 'expires_at'), 'expires_at'),
            accountIds: $this->stringList($data['account_ids'] ?? null, 'account_ids'),
            readonly: $this->requireBool($data, 'readonly'),
        );
    }

    public function getOperationsByDate(
        DateTimeInterface $startDate,
        DateTimeInterface $endDate,
        ?string $accountId = null,
        ?int $limit = null,
    ): OperationCollection {
        if ($startDate > $endDate) {
            throw new InvalidRequestException('Start date must be less than or equal to end date.');
        }

        $resolvedAccountId = $accountId ?? $this->resolveDefaultAccountId();

        /** @var ApiResponse $response */
        $response = $this->accountApi->transactions(
            new TransactionsRequest(
                accountId: $resolvedAccountId,
                limit: $limit,
                interval: new Interval($startDate->getTimestamp(), $endDate->getTimestamp()),
            ),
        );

        $data = $this->extractResponseData(
            $response,
            sprintf('accounts/%s/transactions', $resolvedAccountId),
        );

        return $this->mapOperations($data, $resolvedAccountId);
    }

    public function getOrders(?string $accountId = null): OrderCollection
    {
        $resolvedAccountId = $accountId ?? $this->resolveDefaultAccountId();

        /** @var ApiResponse $response */
        $response = $this->orderApi->orders(new OrdersRequest($resolvedAccountId));

        $data = $this->extractResponseData(
            $response,
            sprintf('accounts/%s/orders', $resolvedAccountId),
        );

        return $this->mapOrders($data, $resolvedAccountId);
    }

    public function getOrder(string $orderId, ?string $accountId = null): OrderDto
    {
        $resolvedAccountId = $accountId ?? $this->resolveDefaultAccountId();

        /** @var ApiResponse $response */
        $response = $this->orderApi->order(new OrderRequest($resolvedAccountId, $orderId));

        $data = $this->extractResponseData(
            $response,
            sprintf('accounts/%s/orders/%s', $resolvedAccountId, $orderId),
        );

        return $this->mapOrder($data, $resolvedAccountId);
    }

    public function placeOrder(PlaceOrderInputDto $order, ?string $accountId = null): OrderDto
    {
        $resolvedAccountId = $accountId ?? $this->resolveDefaultAccountId();

        /** @var ApiResponse $response */
        $response = $this->orderApi->place(
            new PlaceOrderRequest(
                accountId: $resolvedAccountId,
                payload: $order->toPayload(),
            ),
        );

        $data = $this->extractResponseData(
            $response,
            sprintf('accounts/%s/orders', $resolvedAccountId),
        );

        return $this->mapOrder($data, $resolvedAccountId);
    }

    private function resolveDefaultAccountId(): string
    {
        $accountIds = $this->sessionDetails()->accountIds();

        if ($accountIds === []) {
            throw new AccountResolutionException('Session does not contain any account ids.');
        }

        if (count($accountIds) > 1) {
            throw new AccountResolutionException(
                'Session contains multiple accounts. Pass accountId explicitly to avoid ambiguous routing.',
            );
        }

        return $accountIds[0];
    }

    /**
     * @param ApiMap $data
     */
    private function mapOperations(array $data, string $accountId): OperationCollection
    {
        $transactions = $this->listOfArrays($data['transactions'] ?? null, 'transactions');

        $operations = [];

        foreach ($transactions as $transaction) {
            $operations[] = new OperationDto(
                id: $this->requireString($transaction, 'id'),
                accountId: $accountId,
                category: $this->requireString($transaction, 'category'),
                transactionCategory: $this->requireString($transaction, 'transaction_category'),
                transactionName: $this->requireString($transaction, 'transaction_name'),
                symbol: $this->optionalString($transaction, 'symbol') ?? '',
                occurredAt: $this->parseDateTime($this->requireString($transaction, 'timestamp'), 'timestamp'),
                change: $this->mapMoney($this->requireArray($transaction, 'change'), 'change'),
                changeQuantity: $this->optionalString($transaction, 'change_qty'),
                trade: $this->mapTrade($transaction['trade'] ?? null),
            );
        }

        /** @var list<OperationDto> $operations */
        return new OperationCollection($operations);
    }

    /**
     * @param ApiMap $data
     */
    private function mapOrders(array $data, string $accountId): OrderCollection
    {
        $orders = [];

        foreach ($this->listOfArrays($data['orders'] ?? null, 'orders') as $orderData) {
            $orders[] = $this->mapOrder($orderData, $accountId);
        }

        /** @var list<OrderDto> $orders */
        return new OrderCollection($orders);
    }

    /**
     * @param ApiMap $data
     */
    private function mapOrder(array $data, string $accountId): OrderDto
    {
        /** @var ApiMap $orderData */
        $orderData = isset($data['order']) && is_array($data['order']) ? $data['order'] : $data;

        return new OrderDto(
            orderId: $this->requireString($orderData, 'order_id'),
            execId: $this->optionalString($orderData, 'exec_id'),
            status: $this->requireString($orderData, 'status'),
            accountId: $this->optionalString($orderData, 'account_id') ?? $accountId,
            symbol: $this->requireString($orderData, 'symbol'),
            quantity: $this->extractDecimalValue($this->requireArray($orderData, 'quantity'), 'quantity'),
            side: $this->requireString($orderData, 'side'),
            type: $this->requireString($orderData, 'type'),
            timeInForce: $this->requireString($orderData, 'time_in_force'),
            clientOrderId: $this->optionalString($orderData, 'client_order_id'),
            comment: $this->optionalString($orderData, 'comment'),
            limitPrice: $this->extractOptionalDecimalValue($orderData['limit_price'] ?? null, 'limit_price'),
            stopPrice: $this->extractOptionalDecimalValue($orderData['stop_price'] ?? null, 'stop_price'),
            transactAt: $this->optionalDateTime($orderData, 'transact_at'),
            acceptAt: $this->optionalDateTime($orderData, 'accept_at'),
            withdrawAt: $this->optionalDateTime($orderData, 'withdraw_at'),
            initialQuantity: $this->extractOptionalDecimalValue($orderData['initial_quantity'] ?? null, 'initial_quantity'),
            executedQuantity: $this->extractOptionalDecimalValue($orderData['executed_quantity'] ?? null, 'executed_quantity'),
            remainingQuantity: $this->extractOptionalDecimalValue($orderData['remaining_quantity'] ?? null, 'remaining_quantity'),
        );
    }

    /**
     * @param ApiScalar|ApiNestedArray $value
     */
    private function mapTrade($value): ?OperationTradeDto
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw new ResponseMappingException('Field "trade" must be an object or null.');
        }

        return new OperationTradeDto(
            tradeId: $this->requireString($value, 'trade_id'),
            orderId: $this->optionalString($value, 'order_id'),
        );
    }

    /**
     * @param ApiMap $value
     */
    private function mapMoney(array $value, string $field): MoneyDto
    {
        return new MoneyDto(
            currencyCode: $this->requireString($value, 'currency_code'),
            units: $this->requireString($value, 'units'),
            nanos: $this->requireIntLike($value, 'nanos', $field),
        );
    }

    /**
     * @param ApiResponse $response
     *
     * @return ApiMap
     */
    private function extractResponseData(array $response, string $endpoint): array
    {
        $ok = $response['ok'] ?? null;

        if ($ok !== true) {
            $status = $this->optionalInt($response, 'status') ?? 0;
            $headers = $this->headerMap($response['meta']['headers'] ?? null);
            /** @var ApiMap|null $errorPayload */
            $errorPayload = is_array($response['error'] ?? null) ? $response['error'] : null;
            $message = $this->resolveApiErrorMessage($endpoint, $errorPayload);

            throw new ApiHttpException(
                message: $message,
                httpStatus: $status,
                headers: $headers,
                errorPayload: $errorPayload,
                rawBody: $this->optionalString($errorPayload ?? [], 'raw'),
            );
        }

        $data = $response['data'] ?? null;

        if (!is_array($data)) {
            throw new InvalidResponseException(
                sprintf('Response data for endpoint "%s" must be an object.', $endpoint),
            );
        }

        /** @var ApiMap $data */
        return $data;
    }

    private function parseDateTime(string $value, string $field): DateTimeImmutable
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
    private function optionalDateTime(array $data, string $field): ?DateTimeImmutable
    {
        $value = $this->optionalString($data, $field);

        if ($value === null || $value === '') {
            return null;
        }

        return $this->parseDateTime($value, $field);
    }

    /**
     * @param ApiMap $data
     *
     * @return ApiMap
     */
    private function requireArray(array $data, string $field): array
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
    private function requireString(array $data, string $field): string
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
    private function optionalString(array $data, string $field): ?string
    {
        $value = $data[$field] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param ApiMap $data
     */
    private function requireBool(array $data, string $field): bool
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
    private function requireIntLike(array $data, string $field, string $context): int
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
    private function optionalInt(array $data, string $field): ?int
    {
        $value = $data[$field] ?? null;

        if (!is_int($value)) {
            return null;
        }

        return $value;
    }

    /**
     * @param ApiMap $data
     */
    private function extractDecimalValue(array $data, string $field): string
    {
        return $this->requireString($data, 'value');
    }

    /**
     * @param ApiScalar|ApiNestedArray $value
     */
    private function extractOptionalDecimalValue($value, string $field): ?string
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
    private function stringList($value, string $field): array
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
    private function listOfArrays($value, string $field): array
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
    private function headerMap($value): array
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
     * @param ApiMap|null $errorPayload
     */
    private function resolveApiErrorMessage(string $endpoint, ?array $errorPayload): string
    {
        if ($errorPayload !== null) {
            $message = $this->firstStringByKeys($errorPayload, ['message', 'error', 'description', 'detail']);

            if ($message !== null) {
                $code = $this->firstStringByKeys($errorPayload, ['code', 'error_code']);

                if ($code !== null && $code !== '') {
                    return sprintf('[%s] %s', $code, $message);
                }

                return $message;
            }
        }

        return sprintf('Finam API request failed for endpoint "%s".', $endpoint);
    }

    /**
     * @param ApiMap $payload
     * @param list<string> $keys
     */
    private function firstStringByKeys(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $payload[$key] ?? null;

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }
}
