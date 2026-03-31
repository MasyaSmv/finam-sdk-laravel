<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Contracts\Session;

use MasyaSmv\FinamSdk\Dto\Connect\SessionDetailsDto;

interface SessionDetailsServiceInterface
{
    public function sessionDetails(): SessionDetailsDto;
}
