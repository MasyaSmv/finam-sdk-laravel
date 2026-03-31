<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Service;

use DateTimeInterface;
use MasyaSmv\FinamSdk\Collections\OperationCollection;
use MasyaSmv\FinamSdk\Contracts\Api\AccountApiInterface;
use MasyaSmv\FinamSdk\Contracts\Session\ApiResponseDecoderInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionAccountResolverInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionOperationServiceInterface;
use MasyaSmv\FinamSdk\Dto\Account\TransactionsRequest;
use MasyaSmv\FinamSdk\Dto\Shared\Interval;
use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;
use MasyaSmv\FinamSdk\Session\Mapper\OperationMapper;

/**
 * @phpstan-import-type ApiResponse from \MasyaSmv\FinamSdk\Session\Support\ApiValueReader
 * @psalm-import-type ApiResponse from \MasyaSmv\FinamSdk\Session\Support\ApiValueReader
 */
final class SessionOperationService implements SessionOperationServiceInterface
{
    public function __construct(
        private AccountApiInterface $accountApi,
        private SessionAccountResolverInterface $accountResolver,
        private ApiResponseDecoderInterface $decoder,
        private OperationMapper $mapper,
    ) {
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

        $resolvedAccountId = $accountId ?? $this->accountResolver->resolveDefaultAccountId();
        /** @var ApiResponse $response */
        $response = $this->accountApi->transactions(
            new TransactionsRequest(
                accountId: $resolvedAccountId,
                limit: $limit,
                interval: new Interval($startDate->getTimestamp(), $endDate->getTimestamp()),
            ),
        );
        $data = $this->decoder->extractData(
            $response,
            sprintf('accounts/%s/transactions', $resolvedAccountId),
        );

        return $this->mapper->mapCollection($data, $resolvedAccountId);
    }
}
