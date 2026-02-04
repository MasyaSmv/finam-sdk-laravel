<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Api;

use MasyaSmv\FinamSdk\Dto\Connect\TokenDetailsRequest;

interface ConnectApiInterface
{
    /**
     * @return array<string, mixed>
     */
    public function tokenDetails(TokenDetailsRequest $request): array;
}
