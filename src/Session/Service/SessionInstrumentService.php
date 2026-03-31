<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Service;

use MasyaSmv\FinamSdk\Collections\ExchangeCollection;
use MasyaSmv\FinamSdk\Collections\InstrumentCollection;
use MasyaSmv\FinamSdk\Contracts\Api\InstrumentApiInterface;
use MasyaSmv\FinamSdk\Contracts\Session\ApiResponseDecoderInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionInstrumentServiceInterface;
use MasyaSmv\FinamSdk\Dto\Instrument\AssetsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ClockDto;
use MasyaSmv\FinamSdk\Dto\Instrument\ClockRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ExchangesRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\InstrumentDto;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleDto;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleRequest;
use MasyaSmv\FinamSdk\Session\Mapper\ClockMapper;
use MasyaSmv\FinamSdk\Session\Mapper\ExchangeMapper;
use MasyaSmv\FinamSdk\Session\Mapper\InstrumentMapper;
use MasyaSmv\FinamSdk\Session\Mapper\ScheduleMapper;

final class SessionInstrumentService implements SessionInstrumentServiceInterface
{
    public function __construct(
        private InstrumentApiInterface $instrumentApi,
        private ApiResponseDecoderInterface $decoder,
        private InstrumentMapper $mapper,
        private ExchangeMapper $exchangeMapper,
        private ClockMapper $clockMapper,
        private ScheduleMapper $scheduleMapper,
    ) {
    }

    public function getInstruments(): InstrumentCollection
    {
        $response = $this->instrumentApi->assets(new AssetsRequest());
        $data = $this->decoder->extractData($response, 'assets');

        return $this->mapper->mapCollection($data);
    }

    public function getInstrument(string $symbol, ?string $accountId = null): InstrumentDto
    {
        $response = $this->instrumentApi->asset(new GetAssetRequest($symbol, $accountId));
        $data = $this->decoder->extractData($response, 'assets/asset');

        return $this->mapper->map($data);
    }

    public function getExchanges(): ExchangeCollection
    {
        $response = $this->instrumentApi->exchanges(new ExchangesRequest());
        $data = $this->decoder->extractData($response, 'assets/exchanges');

        return $this->exchangeMapper->mapCollection($data);
    }

    public function getClock(): ClockDto
    {
        $response = $this->instrumentApi->clock(new ClockRequest());
        $data = $this->decoder->extractData($response, 'assets/clock');

        return $this->clockMapper->map($data);
    }

    public function getSchedule(string $symbol): ScheduleDto
    {
        $response = $this->instrumentApi->schedule(new ScheduleRequest($symbol));
        $data = $this->decoder->extractData($response, 'assets/schedule');

        return $this->scheduleMapper->map($data);
    }
}
