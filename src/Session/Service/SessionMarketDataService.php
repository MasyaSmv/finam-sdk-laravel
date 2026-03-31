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

        $quotes = [];

        foreach ($symbols as $symbol) {
            if (!is_string($symbol) || $symbol === '') {
                throw new InvalidRequestException('Each symbol must be a non-empty string.');
            }

            $response = $this->marketApi->quotes(new QuotesRequest($symbol));
            $data = $this->decoder->extractData(
                $response,
                sprintf('instruments/%s/quotes/latest', $symbol),
            );
            $quotes[] = $this->quoteMapper->map($data);
        }

        /** @var list<\MasyaSmv\FinamSdk\Dto\Market\QuoteDto> $quotes */
        return $this->quoteMapper->mapCollection($quotes);
    }

    public function getCandles(CandlesQueryDto $query): CandleCollection
    {
        $response = $this->marketApi->candles(new CandlesRequest($query));
        $data = $this->decoder->extractData(
            $response,
            sprintf('instruments/%s/bars', $query->symbol()),
        );

        return $this->candleMapper->mapCollection($data);
    }
}
