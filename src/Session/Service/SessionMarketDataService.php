<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Service;

use MasyaSmv\FinamSdk\Collections\CandleCollection;
use MasyaSmv\FinamSdk\Collections\QuoteCollection;
use MasyaSmv\FinamSdk\Contracts\Api\MarketApiInterface;
use MasyaSmv\FinamSdk\Contracts\Session\ApiResponseDecoderInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionMarketDataServiceInterface;
use MasyaSmv\FinamSdk\Dto\Market\CandlesQueryDto;
use MasyaSmv\FinamSdk\Dto\Market\CandlesRequest;
use MasyaSmv\FinamSdk\Dto\Market\QuotesRequest;
use MasyaSmv\FinamSdk\Exceptions\InvalidRequestException;
use MasyaSmv\FinamSdk\Session\Mapper\CandleMapper;
use MasyaSmv\FinamSdk\Session\Mapper\QuoteMapper;
final class SessionMarketDataService implements SessionMarketDataServiceInterface
{
    public function __construct(
        private MarketApiInterface $marketApi,
        private ApiResponseDecoderInterface $decoder,
        private QuoteMapper $quoteMapper,
        private CandleMapper $candleMapper,
    ) {
    }

    public function getLatestQuotes(array $symbols): QuoteCollection
    {
        if ($symbols === []) {
            throw new InvalidRequestException('Symbols list must not be empty.');
        }

        $response = $this->marketApi->quotes(new QuotesRequest(['symbols' => $symbols]));
        $data = $this->decoder->extractData($response, 'market/quotes');

        return $this->quoteMapper->mapCollection($data);
    }

    public function getCandles(CandlesQueryDto $query): CandleCollection
    {
        $response = $this->marketApi->candles(new CandlesRequest($query->toQuery()));
        $data = $this->decoder->extractData($response, 'market/candles');

        return $this->candleMapper->mapCollection($data);
    }
}
