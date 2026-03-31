<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Dto\Transport;

final class ApiHeaders
{
    /**
     * @param array<string, list<string>> $headers
     */
    public function __construct(private array $headers = [])
    {
    }

    public function firstValueByNames(string ...$names): ?string
    {
        foreach ($names as $name) {
            foreach ($this->headers as $headerName => $values) {
                if (mb_strtolower($headerName) !== $name) {
                    continue;
                }

                foreach ($values as $value) {
                    if ($value !== '') {
                        return $value;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, list<string>>
     */
    public function toArray(): array
    {
        return $this->headers;
    }
}
