<?php

declare(strict_types=1);

namespace He4rt\Meeting\Entities;

use DateTimeImmutable;
use DateTimeInterface;

final class MeetingEntity
{
    public function __construct(
        public string $id,
        public ?string $content,
        public int $meetingTypeId,
        public string $adminId,
        public DateTimeImmutable $startsAt,
        public ?DateTimeImmutable $endsAt,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {}

    public static function make(array $payload): self
    {
        $toImmutable = fn ($value) => $value instanceof DateTimeInterface
            ? DateTimeImmutable::createFromMutable($value)
            : new DateTimeImmutable($value);

        return new self(
            id: $payload['id'],
            content: $payload['content'] ?? null,
            meetingTypeId: $payload['meeting_type_id'],
            adminId: $payload['admin_id'],
            startsAt: $toImmutable($payload['starts_at']),
            endsAt: isset($payload['ends_at']) ? $toImmutable($payload['ends_at']) : null,
            createdAt: $toImmutable($payload['created_at']),
            updatedAt: $toImmutable($payload['updated_at'])
        );
    }
}
