<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Service;

use MasyaSmv\FinamSdk\Contracts\Api\ConnectApiInterface;
use MasyaSmv\FinamSdk\Contracts\Session\ApiResponseDecoderInterface;
use MasyaSmv\FinamSdk\Contracts\Session\SessionDetailsServiceInterface;
use MasyaSmv\FinamSdk\Dto\Connect\SessionDetailsDto;
use MasyaSmv\FinamSdk\Session\Mapper\SessionDetailsMapper;
final class SessionDetailsService implements SessionDetailsServiceInterface
{
    public function __construct(
        private ConnectApiInterface $connectApi,
        private ApiResponseDecoderInterface $decoder,
        private SessionDetailsMapper $mapper,
    ) {
    }

    public function sessionDetails(): SessionDetailsDto
    {
        $response = $this->connectApi->tokenDetails();
        $data = $this->decoder->extractData($response, 'sessions/details');

        return $this->mapper->map($data);
    }
}
