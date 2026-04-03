<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Service;

use MasyaSmv\FinamSdk\Collections\ExchangeCollection;
use MasyaSmv\FinamSdk\Collections\InstrumentCollection;
use MasyaSmv\FinamSdk\Contracts\Api\InstrumentApiInterface;
use MasyaSmv\FinamSdk\Contracts\Session\ApiResponseDecoderInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionAccountResolverInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionInstrumentServiceInterface;
use MasyaSmv\FinamSdk\Dto\Instrument\AllAssetsPageDto;
use MasyaSmv\FinamSdk\Dto\Instrument\AllAssetsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ClockDto;
use MasyaSmv\FinamSdk\Dto\Instrument\ClockRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\ExchangesRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\InstrumentDto;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleDto;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleRequest;
use MasyaSmv\FinamSdk\Session\Mapper\AllAssetsMapper;
use MasyaSmv\FinamSdk\Session\Mapper\ClockMapper;
use MasyaSmv\FinamSdk\Session\Mapper\ExchangeMapper;
use MasyaSmv\FinamSdk\Session\Mapper\InstrumentMapper;
use MasyaSmv\FinamSdk\Session\Mapper\ScheduleMapper;

final class SessionInstrumentService implements SessionInstrumentServiceInterface
{
    public function __construct(
        private InstrumentApiInterface $instrumentApi,
        private SessionAccountResolverInterface $accountResolver,
        private ApiResponseDecoderInterface $decoder,
        private InstrumentMapper $mapper,
        private AllAssetsMapper $allAssetsMapper,
        private ExchangeMapper $exchangeMapper,
        private ClockMapper $clockMapper,
        private ScheduleMapper $scheduleMapper,
    ) {
    }

    public function getAllInstruments(
        ?int $cursor = null,
        bool $onlyActive = false,
        bool $onlyDisabled = false,
    ): AllAssetsPageDto {
        $response = $this->instrumentApi->allAssets(new AllAssetsRequest($cursor, $onlyActive, $onlyDisabled));
        $data = $this->decoder->extractData($response, 'assets/all');

        return $this->allAssetsMapper->map($data);
    }

    public function getInstruments(): InstrumentCollection
    {
        $cursor = null;
        $items = [];

        do {
            $response = $this->instrumentApi->allAssets(new AllAssetsRequest($cursor));
            $data = $this->decoder->extractData($response, 'assets/all');
            $page = $this->allAssetsMapper->map($data);

            foreach ($page->assets()->all() as $instrument) {
                $items[] = $instrument;
            }

            $cursor = $page->nextCursor();
        } while ($cursor !== null);

        return new InstrumentCollection($items);
    }

    public function getInstrument(string $symbol, ?string $accountId = null): InstrumentDto
    {
        $resolvedAccountId = $accountId ?? $this->accountResolver->resolveDefaultAccountId();
        $response = $this->instrumentApi->asset(new GetAssetRequest($symbol, $resolvedAccountId));
        $data = $this->decoder->extractData($response, 'assets/asset');

        return $this->mapper->map($data);
    }

    public function getExchanges(): ExchangeCollection
    {
        $response = $this->instrumentApi->exchanges(new ExchangesRequest());
        $data = $this->decoder->extractData($response, 'exchanges');

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
        $data = $this->decoder->extractData($response, sprintf('assets/%s/schedule', $symbol));

        return $this->scheduleMapper->map($data);
    }
}
