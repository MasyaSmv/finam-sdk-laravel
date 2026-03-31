<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Service;

use MasyaSmv\FinamSdk\Contracts\Session\SessionAccountResolverInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionDetailsServiceInterface;
use MasyaSmv\FinamSdk\Exceptions\AccountResolutionException;

final class SessionAccountResolver implements SessionAccountResolverInterface
{
    public function __construct(private SessionDetailsServiceInterface $detailsService)
    {
    }

    public function resolveDefaultAccountId(): string
    {
        $accountIds = $this->detailsService->sessionDetails()->accountIds();

        if ($accountIds->isEmpty()) {
            throw new AccountResolutionException('Session does not contain any account ids.');
        }

        if ($accountIds->count() > 1) {
            throw new AccountResolutionException(
                'Session contains multiple accounts. Pass accountId explicitly to avoid ambiguous routing.',
            );
        }

        /** @var string $accountId */
        $accountId = $accountIds->first();

        return $accountId;
    }
}
