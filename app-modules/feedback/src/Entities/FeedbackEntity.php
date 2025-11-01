<?php

declare(strict_types=1);

namespace He4rt\Feedback\Entities;

use JsonSerializable;

final readonly class FeedbackEntity implements JsonSerializable
{
    public function __construct(
        private string $id,
        private string $senderId,
        private string $targetId,
        private string $type,
        private string $message,
    ) {}

    public static function make(array $payload): self
    {
        return new self(
            id: $payload['id'],
            senderId: $payload['sender_id'],
            targetId: $payload['target_id'],
            type: $payload['type'],
            message: $payload['message'],
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'sender_id' => $this->senderId,
            'target_id' => $this->targetId,
            'type' => $this->type,
            'message' => $this->message,
        ];
    }
}
