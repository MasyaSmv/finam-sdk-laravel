<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Tests;

use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Exceptions\ResponseMappingException;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

final class TransportPayloadTest extends TestCase
{
    public function testIntNormalizesNumericString(): void
    {
        $payload = new ApiPayload([
            'nanos' => '42',
        ]);

        $this->assertSame(42, $payload->int('nanos'));
    }

    public function testRequireIntRejectsNonNumericString(): void
    {
        $reader = new ApiValueReader();
        $payload = new ApiPayload([
            'nanos' => 'forty-two',
        ]);

        $this->expectException(ResponseMappingException::class);

        $reader->requireInt($payload, 'nanos', 'money');
    }
}
