<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Session;

interface SessionAccountResolverInterface
{
    public function resolveDefaultAccountId(): string;
}
