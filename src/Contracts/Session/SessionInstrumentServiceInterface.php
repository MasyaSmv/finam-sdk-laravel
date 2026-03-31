<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Session;

use MasyaSmv\FinamSdk\Collections\ExchangeCollection;
use MasyaSmv\FinamSdk\Collections\InstrumentCollection;
use MasyaSmv\FinamSdk\Dto\Instrument\ClockDto;
use MasyaSmv\FinamSdk\Dto\Instrument\InstrumentDto;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleDto;

interface SessionInstrumentServiceInterface
{
    public function getInstruments(): InstrumentCollection;

    public function getInstrument(string $symbol, ?string $accountId = null): InstrumentDto;

    public function getExchanges(): ExchangeCollection;

    public function getClock(): ClockDto;

    public function getSchedule(string $symbol): ScheduleDto;
}
