<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Session;

use MasyaSmv\FinamSdk\Collections\InstrumentCollection;
use MasyaSmv\FinamSdk\Dto\Instrument\InstrumentDto;

interface SessionInstrumentServiceInterface
{
    public function getInstruments(): InstrumentCollection;

    public function getInstrument(string $symbol, ?string $accountId = null): InstrumentDto;
}
