<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Session;

use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

/**
 * @phpstan-import-type ApiMap from ApiValueReader
 * @phpstan-import-type ApiResponse from ApiValueReader
 * @psalm-import-type ApiMap from ApiValueReader
 * @psalm-import-type ApiResponse from ApiValueReader
 */
interface ApiResponseDecoderInterface
{
    /**
     * @param ApiResponse $response
     *
     * @return ApiMap
     */
    public function extractData(array $response, string $endpoint): array;
}
