<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Collections\OperationCollection;
use MasyaSmv\FinamSdk\Dto\Account\OperationDto;
use MasyaSmv\FinamSdk\Dto\Account\OperationTradeDto;
use MasyaSmv\FinamSdk\Dto\Shared\MoneyDto;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Exceptions\ResponseMappingException;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

final class OperationMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    public function mapCollection(ApiPayload $data, string $accountId): OperationCollection
    {
        $transactions = $this->reader->requireObjectList($data, 'transactions');

        $operations = [];

        foreach ($transactions->payloads() as $transaction) {
            $operations[] = new OperationDto(
                id: $this->reader->requireString($transaction, 'id'),
                accountId: $accountId,
                category: $this->reader->requireString($transaction, 'category'),
                transactionCategory: $this->reader->requireString($transaction, 'transaction_category'),
                transactionName: $this->reader->requireString($transaction, 'transaction_name'),
                symbol: $this->reader->optionalString($transaction, 'symbol') ?? '',
                occurredAt: $this->reader->parseDateTime($this->reader->requireString($transaction, 'timestamp'), 'timestamp'),
                change: $this->mapMoney($this->reader->requireObject($transaction, 'change'), 'change'),
                changeQuantity: $this->reader->optionalString($transaction, 'change_qty'),
                trade: $this->mapTrade($this->reader->optionalObject($transaction, 'trade')),
            );
        }

        /** @var list<OperationDto> $operations */
        return new OperationCollection($operations);
    }

    private function mapTrade(?ApiPayload $value): ?OperationTradeDto
    {
        if ($value === null) {
            return null;
        }

        return new OperationTradeDto(
            tradeId: $this->reader->requireString($value, 'trade_id'),
            orderId: $this->reader->optionalString($value, 'order_id'),
        );
    }

    private function mapMoney(ApiPayload $value, string $field): MoneyDto
    {
        return new MoneyDto(
            currencyCode: $this->reader->requireString($value, 'currency_code'),
            units: $this->reader->requireString($value, 'units'),
            nanos: $this->reader->requireInt($value, 'nanos', $field),
        );
    }
}
