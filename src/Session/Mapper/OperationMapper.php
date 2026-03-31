<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Collections\OperationCollection;
use MasyaSmv\FinamSdk\Dto\Account\OperationDto;
use MasyaSmv\FinamSdk\Dto\Account\OperationTradeDto;
use MasyaSmv\FinamSdk\Dto\Shared\MoneyDto;
use MasyaSmv\FinamSdk\Exceptions\ResponseMappingException;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

/**
 * @phpstan-import-type ApiMap from ApiValueReader
 * @phpstan-import-type ApiNestedArray from ApiValueReader
 * @phpstan-import-type ApiScalar from ApiValueReader
 * @psalm-import-type ApiMap from ApiValueReader
 * @psalm-import-type ApiNestedArray from ApiValueReader
 * @psalm-import-type ApiScalar from ApiValueReader
 */
final class OperationMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    /**
     * @param ApiMap $data
     */
    public function mapCollection(array $data, string $accountId): OperationCollection
    {
        $transactions = $this->reader->listOfArrays($data['transactions'] ?? null, 'transactions');

        $operations = [];

        foreach ($transactions as $transaction) {
            $operations[] = new OperationDto(
                id: $this->reader->requireString($transaction, 'id'),
                accountId: $accountId,
                category: $this->reader->requireString($transaction, 'category'),
                transactionCategory: $this->reader->requireString($transaction, 'transaction_category'),
                transactionName: $this->reader->requireString($transaction, 'transaction_name'),
                symbol: $this->reader->optionalString($transaction, 'symbol') ?? '',
                occurredAt: $this->reader->parseDateTime($this->reader->requireString($transaction, 'timestamp'), 'timestamp'),
                change: $this->mapMoney($this->reader->requireArray($transaction, 'change'), 'change'),
                changeQuantity: $this->reader->optionalString($transaction, 'change_qty'),
                trade: $this->mapTrade($transaction['trade'] ?? null),
            );
        }

        /** @var list<OperationDto> $operations */
        return new OperationCollection($operations);
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
            tradeId: $this->reader->requireString($value, 'trade_id'),
            orderId: $this->reader->optionalString($value, 'order_id'),
        );
    }

    /**
     * @param ApiMap $value
     */
    private function mapMoney(array $value, string $field): MoneyDto
    {
        return new MoneyDto(
            currencyCode: $this->reader->requireString($value, 'currency_code'),
            units: $this->reader->requireString($value, 'units'),
            nanos: $this->reader->requireIntLike($value, 'nanos', $field),
        );
    }
}
