<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Facades;

use Illuminate\Support\Facades\Facade;
use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Dto\Auth\IssuedTokenDto;
use MasyaSmv\FinamSdk\Contracts\FinamSessionInterface;

/**
 * @method static IssuedTokenDto issueToken(string $secret)
 * @method static FinamSessionInterface connect(string $token)
 * @method static FinamClient client(string $token)
 *
 * @see \MasyaSmv\FinamSdk\FinamManager
 */
final class Finam extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'finam';
    }
}
