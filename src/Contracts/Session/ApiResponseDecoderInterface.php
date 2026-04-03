<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Session;

use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;

interface ApiResponseDecoderInterface
{
    public function extractData(ApiResponse $response, string $endpoint): ApiPayload;
}
