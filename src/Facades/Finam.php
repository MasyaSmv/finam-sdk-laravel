<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Facades;

use Illuminate\Support\Facades\Facade;
use MasyaSmv\FinamSdk\Client\FinamClient;
use MasyaSmv\FinamSdk\Contracts\FinamSessionInterface;

/**
 * @method static FinamSessionInterface connect(string $token)
 * @method static FinamClient client(?string $token = null)
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
