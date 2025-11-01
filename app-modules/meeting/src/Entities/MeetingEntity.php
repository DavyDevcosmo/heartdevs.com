<?php

declare(strict_types=1);

namespace He4rt\Meeting\Entities;

use DateTimeImmutable;

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
        $endsAt = empty($payload['ends_at'])
            ? null
            : new DateTimeImmutable($payload['ends_at']);

        return new self(
            id: $payload['id'],
            content: $payload['content'] ?? null,
            meetingTypeId: $payload['meeting_type_id'],
            adminId: $payload['admin_id'],
            startsAt: new DateTimeImmutable($payload['starts_at']),
            endsAt: $endsAt,
            createdAt: new DateTimeImmutable($payload['created_at']),
            updatedAt: new DateTimeImmutable($payload['updated_at'])
        );
    }
}
