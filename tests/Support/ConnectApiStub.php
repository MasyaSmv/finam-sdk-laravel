<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests\Support;

use MasyaSmv\FinamSdk\Contracts\Api\ConnectApiInterface;
use MasyaSmv\FinamSdk\Dto\Transport\ApiResponse;
final class ConnectApiStub implements ConnectApiInterface
{
    public function __construct(private ApiResponse $response)
    {
    }

    public function tokenDetails(): ApiResponse
    {
        return $this->response;
    }
}
