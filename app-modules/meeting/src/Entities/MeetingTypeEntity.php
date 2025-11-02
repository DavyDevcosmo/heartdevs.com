<?php

declare(strict_types=1);

namespace He4rt\Meeting\Entities;

use DateTimeImmutable;
use DateTimeInterface;

final readonly class MeetingTypeEntity
{
    public function __construct(
        public int $id,
        public string $name,
        public int $weekDay,
        public DateTimeImmutable $startAt
    ) {}

    public static function make(array $payload): self
    {
        $toImmutable = fn ($value) => $value instanceof DateTimeInterface
            ? DateTimeImmutable::createFromMutable($value)
            : new DateTimeImmutable($value);

        return new self(
            id: $payload['id'],
            name: $payload['name'],
            weekDay: $payload['week_day'],
            startAt: $toImmutable($payload['start_at']),
        );
    }

    public function getMeetingDayForHumans(): string
    {
        $days = [
            0 => 'Domingo',
            1 => 'Segunda Feira',
            2 => 'Terça Feira',
            3 => 'Quarta Feira',
            4 => 'Quinta Feira',
            5 => 'Sexta Feira',
            6 => 'Sábado',
        ];

        return $days[$this->weekDay];
    }
}
