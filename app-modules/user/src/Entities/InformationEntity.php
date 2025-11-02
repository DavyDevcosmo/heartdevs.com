<?php

declare(strict_types=1);

namespace He4rt\User\Entities;

use JsonSerializable;

final class InformationEntity implements JsonSerializable
{
    public function __construct(
        private readonly string $id,
        private readonly string $userId,
        private ?string $name,
        private ?string $nickname,
        private ?string $linkedinUrl,
        private ?string $githubUrl,
        private ?string $birthdate,
        private ?string $about,
    ) {}

    public static function make(array $payload): self
    {
        return new self(
            id: $payload['id'],
            userId: $payload['user_id'],
            name: $payload['name'],
            nickname: $payload['nickname'],
            linkedinUrl: $payload['linkedin_url'],
            githubUrl: $payload['github_url'],
            birthdate: $payload['birthdate'],
            about: $payload['about'],
        );
    }

    public function update(array $payload): void
    {
        $this->name = $payload['name'] ?? $this->name;
        $this->nickname = $payload['nickname'] ?? $this->nickname;
        $this->about = $payload['about'] ?? $this->about;
        $this->githubUrl = $payload['github_url'] ?? $this->githubUrl;
        $this->linkedinUrl = $payload['linkedin_url'] ?? $this->linkedinUrl;
        $this->birthdate = $payload['birthdate'] ?? $this->birthdate;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'name' => $this->name,
            'nickname' => $this->nickname,
            'linkedin_url' => $this->linkedinUrl,
            'github_url' => $this->githubUrl,
            'birthdate' => $this->birthdate,
            'about' => $this->about,
        ];
    }
}
