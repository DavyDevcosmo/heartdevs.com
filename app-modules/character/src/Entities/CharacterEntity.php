<?php

declare(strict_types=1);

namespace He4rt\Character\Entities;

use JsonSerializable;

final class CharacterEntity implements JsonSerializable
{
    public LevelEntity $level;

    public DailyRewardEntity $dailyReward;

    public ReputationEntity $reputation;

    public function __construct(
        public string $id,
        public string $userId,
        int $reputation,
        int $experience,
        ?string $claimedAt = null
    ) {
        $this->reputation = new ReputationEntity($reputation);
        $this->level = new LevelEntity($experience);
        $this->dailyReward = new DailyRewardEntity($claimedAt);
    }

    public static function make(array $payload): self
    {
        return new self(
            id: $payload['id'],
            userId: $payload['user_id'],
            reputation: $payload['reputation'],
            experience: $payload['experience'],
            claimedAt: $payload['daily_bonus_claimed_at']
        );
    }

    public function getLevel(): int
    {
        return $this->level->getLevel();
    }

    public function toDatabase(): array
    {
        return [
            'user_id' => $this->userId,
            'reputation' => $this->reputation,
            'experience' => $this->level->getExperience(),
            'daily_bonus_claimed_at' => $this->dailyReward->claimedAt,
        ];
    }

    public function jsonSerialize(): array
    {
        return [
            'user_id' => $this->userId,
            'reputation' => $this->reputation->getPoints(),
            'level' => $this->level->getLevel(),
            'experience' => $this->level->getExperience(),
            'daily_bonus_claimed_at' => $this->dailyReward->claimedAt,
            'percentage_experience' => $this->level->getPercentageExperience(),
        ];
    }
}
