<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Session;

use MasyaSmv\FinamSdk\Collections\CandleCollection;
use MasyaSmv\FinamSdk\Collections\QuoteCollection;
use MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto;

interface SessionMarketDataServiceInterface
{
    /**
     * @param list<string> $symbols
     */
    public function getLatestQuotes(array $symbols): QuoteCollection;

    public function getCandles(CandlesQueryDto $query): CandleCollection;
}
