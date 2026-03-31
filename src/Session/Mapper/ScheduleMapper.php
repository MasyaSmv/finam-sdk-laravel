<?php

declare(strict_types=1);

namespace MasyaSmv\FinamSdk\Session\Mapper;

use MasyaSmv\FinamSdk\Collections\ScheduleSessionCollection;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleDto;
use MasyaSmv\FinamSdk\Dto\Instrument\ScheduleSessionDto;
use MasyaSmv\FinamSdk\Dto\Transport\ApiPayload;
use MasyaSmv\FinamSdk\Session\Support\ApiValueReader;

final class ScheduleMapper
{
    public function __construct(private ApiValueReader $reader)
    {
    }

    public function map(ApiPayload $data): ScheduleDto
    {
        $sessions = [];

        foreach ($this->reader->requireObjectList($data, 'sessions')->payloads() as $sessionData) {
            $interval = $this->reader->requireObject($sessionData, 'interval');
            $startTime = $this->reader->requireObject($interval, 'start_time');
            $endTime = $this->reader->requireObject($interval, 'end_time');

            $sessions[] = new ScheduleSessionDto(
                type: $this->reader->requireString($sessionData, 'type'),
                startAt: $this->reader->timestampFromPayload($startTime, 'start_time'),
                endAt: $this->reader->timestampFromPayload($endTime, 'end_time'),
            );
        }

        /** @var list<ScheduleSessionDto> $sessions */
        return new ScheduleDto(
            symbol: $this->reader->requireString($data, 'symbol'),
            sessions: new ScheduleSessionCollection($sessions),
        );
    }
}
