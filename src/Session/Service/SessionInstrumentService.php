<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Service;

use MasyaSmv\FinamSdk\Collections\InstrumentCollection;
use MasyaSmv\FinamSdk\Contracts\Api\InstrumentApiInterface;
use MasyaSmv\FinamSdk\Contracts\Session\ApiResponseDecoderInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionInstrumentServiceInterface;
use MasyaSmv\FinamSdk\Dto\Instrument\AssetsRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\GetAssetRequest;
use MasyaSmv\FinamSdk\Dto\Instrument\InstrumentDto;
use MasyaSmv\FinamSdk\Session\Mapper\InstrumentMapper;
final class SessionInstrumentService implements SessionInstrumentServiceInterface
{
    public function __construct(
        private InstrumentApiInterface $instrumentApi,
        private ApiResponseDecoderInterface $decoder,
        private InstrumentMapper $mapper,
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
}
