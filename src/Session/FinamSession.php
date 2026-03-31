<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session;

use DateTimeImmutable;
use DateTimeInterface;
use MasyaSmv\FinamSdk\Collections\CandleCollection;
use MasyaSmv\FinamSdk\Collections\InstrumentCollection;
use MasyaSmv\FinamSdk\Collections\OperationCollection;
use MasyaSmv\FinamSdk\Collections\OrderCollection;
use MasyaSmv\FinamSdk\Collections\QuoteCollection;
use MasyaSmv\FinamSdk\Contracts\Api\AccountApiInterface;
use MasyaSmv\FinamSdk\Contracts\Api\ConnectApiInterface;
use MasyaSmv\FinamSdk\Contracts\Api\InstrumentApiInterface;
use MasyaSmv\FinamSdk\Contracts\Api\MarketApiInterface;
use MasyaSmv\FinamSdk\Contracts\Api\OrderApiInterface;
use MasyaSmv\FinamSdk\Contracts\FinamSessionInterface;
use MasyaSmv\FinamSdk\Dto\Account\OperationDto;
use MasyaSmv\FinamSdk\Dto\Account\OperationTradeDto;
use MasyaSmv\FinamSdk\Dto\Account\TransactionsRequest;
use MasyaSmv\FinamSdk\Dto\Connect\SessionDetailsDto;
use MasyaSmv\FinamSdk\Dto\Instrument\AssetsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\InstrumentDto;
use MasyaSmv\FinamSdk\Dto\Market\CandleDto;
use MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto;
use MasyaSmv\FinamSdk\Dto\Market\CandlesRequest;
use MasyaSmv\FinamSdk\Dto\Market\QuoteDto;
use MasyaSmv\FinamSdk\Dto\Market\QuotesRequest;
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
        private InstrumentApiInterface $instrumentApi,
        private MarketApiInterface $marketApi,
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

    public function getInstruments(): InstrumentCollection
    {
        /** @var ApiResponse $response */
        $response = $this->instrumentApi->assets(new AssetsRequest());

        $data = $this->extractResponseData(
            $response,
            'assets',
        );

        return $this->mapInstruments($data);
    }

    public function getInstrument(string $symbol, ?string $accountId = null): InstrumentDto
    {
        /** @var ApiResponse $response */
        $response = $this->instrumentApi->asset(new GetAssetRequest($symbol, $accountId));

        $data = $this->extractResponseData(
            $response,
            'assets/asset',
        );

        return $this->mapInstrument($data);
    }

    public function getLatestQuotes(array $symbols): QuoteCollection
    {
        if ($symbols === []) {
            throw new InvalidRequestException('Symbols list must not be empty.');
        }

        /** @var ApiResponse $response */
        $response = $this->marketApi->quotes(new QuotesRequest(['symbols' => $symbols]));

        $data = $this->extractResponseData(
            $response,
            'market/quotes',
        );

        return $this->mapQuotes($data);
    }

    public function getCandles(CandlesQueryDto $query): CandleCollection
    {
        /** @var ApiResponse $response */
        $response = $this->marketApi->candles(new CandlesRequest($query->toQuery()));

        $data = $this->extractResponseData(
            $response,
            'market/candles',
        );

        return $this->mapCandles($data);
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
     * @param ApiMap $data
     */
    private function mapInstruments(array $data): InstrumentCollection
    {
        $instruments = [];

        foreach ($this->listOfArrays($data['assets'] ?? null, 'assets') as $asset) {
            $instruments[] = $this->mapInstrument($asset);
        }

        /** @var list<InstrumentDto> $instruments */
        return new InstrumentCollection($instruments);
    }

    /**
     * @param ApiMap $data
     */
    private function mapInstrument(array $data): InstrumentDto
    {
        /** @var ApiMap $instrumentData */
        $instrumentData = isset($data['asset']) && is_array($data['asset']) ? $data['asset'] : $data;

        return new InstrumentDto(
            symbol: $this->requireString($instrumentData, 'symbol'),
            shortName: $this->optionalString($instrumentData, 'short_name')
                ?? $this->optionalString($instrumentData, 'name')
                ?? $this->requireString($instrumentData, 'symbol'),
            description: $this->optionalString($instrumentData, 'description'),
            market: $this->optionalString($instrumentData, 'market'),
            currency: $this->optionalString($instrumentData, 'currency'),
            lotSize: $this->extractOptionalDecimalValue($instrumentData['lot_size'] ?? null, 'lot_size'),
            isin: $this->optionalString($instrumentData, 'isin'),
        );
    }

    /**
     * @param ApiMap $data
     */
    private function mapQuotes(array $data): QuoteCollection
    {
        $quotes = [];

        foreach ($this->listOfArrays($data['quotes'] ?? null, 'quotes') as $quoteData) {
            $quotes[] = new QuoteDto(
                symbol: $this->requireString($quoteData, 'symbol'),
                price: $this->extractOptionalDecimalValue($quoteData['price'] ?? null, 'price') ?? '0',
                change: $this->extractOptionalDecimalValue($quoteData['change'] ?? null, 'change'),
                percentChange: $this->extractOptionalDecimalValue($quoteData['change_percent'] ?? null, 'change_percent'),
                timestamp: $this->optionalDateTime($quoteData, 'timestamp'),
            );
        }

        /** @var list<QuoteDto> $quotes */
        return new QuoteCollection($quotes);
    }

    /**
     * @param ApiMap $data
     */
    private function mapCandles(array $data): CandleCollection
    {
        $candles = [];

        foreach ($this->listOfArrays($data['candles'] ?? null, 'candles') as $candleData) {
            $candles[] = new CandleDto(
                timestamp: $this->parseDateTime($this->requireString($candleData, 'timestamp'), 'timestamp'),
                open: $this->extractDecimalValue($this->requireArray($candleData, 'open'), 'open'),
                high: $this->extractDecimalValue($this->requireArray($candleData, 'high'), 'high'),
                low: $this->extractDecimalValue($this->requireArray($candleData, 'low'), 'low'),
                close: $this->extractDecimalValue($this->requireArray($candleData, 'close'), 'close'),
                volume: $this->extractOptionalDecimalValue($candleData['volume'] ?? null, 'volume'),
            );
        }

        /** @var list<CandleDto> $candles */
        return new CandleCollection($candles);
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
            $finamMessage = $this->resolveFinamMessage($errorPayload);
            $finamCode = $this->resolveFinamCode($errorPayload);
            $message = $this->resolveApiErrorMessage($endpoint, $errorPayload);

            throw new ApiHttpException(
                message: $message,
                httpStatus: $status,
                endpoint: $endpoint,
                requestId: $this->resolveRequestId($headers, $errorPayload),
                finamCode: $finamCode,
                finamMessage: $finamMessage,
                requestContext: $this->requestContext($response),
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
        $message = $this->resolveFinamMessage($errorPayload);

        if ($message !== null) {
            $code = $this->resolveFinamCode($errorPayload);

            if ($code !== null && $code !== '') {
                return sprintf('[%s] %s', $code, $message);
            }

            return $message;
        }

        return sprintf('Finam API request failed for endpoint "%s".', $endpoint);
    }

    /**
     * @param ApiMap|null $errorPayload
     */
    private function resolveFinamMessage(?array $errorPayload): ?string
    {
        if ($errorPayload === null) {
            return null;
        }

        return $this->firstStringByKeys($errorPayload, ['message', 'error', 'description', 'detail']);
    }

    /**
     * @param ApiMap|null $errorPayload
     */
    private function resolveFinamCode(?array $errorPayload): ?string
    {
        if ($errorPayload === null) {
            return null;
        }

        return $this->firstStringByKeys($errorPayload, ['code', 'error_code']);
    }

    /**
     * @param HeaderMap $headers
     * @param ApiMap|null $errorPayload
     */
    private function resolveRequestId(array $headers, ?array $errorPayload): ?string
    {
        $requestId = $this->firstHeaderValueByNames($headers, ['x-request-id', 'x-correlation-id', 'request-id']);

        if ($requestId !== null) {
            return $requestId;
        }

        if ($errorPayload === null) {
            return null;
        }

        return $this->firstStringByKeys($errorPayload, ['request_id', 'correlation_id']);
    }

    /**
     * @param ApiResponse $response
     *
     * @return array<string, scalar|array<int|string, scalar|null>|null>
     */
    private function requestContext(array $response): array
    {
        $meta = $response['meta'] ?? null;

        if (!is_array($meta)) {
            return [];
        }

        $request = $meta['request'] ?? null;

        if (!is_array($request)) {
            return [];
        }

        /** @var array<string, scalar|array<int|string, scalar|null>|null> $request */
        $request = $request;

        $context = [];

        foreach ($request as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $context[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                $nested = [];

                foreach ($value as $nestedKey => $nestedValue) {
                    if ((is_int($nestedKey) || is_string($nestedKey)) && is_scalar($nestedValue)) {
                        $nested[$nestedKey] = $nestedValue;
                    }
                }

                $context[$key] = $nested;
            }
        }

        return $context;
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

    /**
     * @param HeaderMap $headers
     * @param list<string> $names
     */
    private function firstHeaderValueByNames(array $headers, array $names): ?string
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
