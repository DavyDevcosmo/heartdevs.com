<?php

declare(strict_types=1);

namespace He4rt\Character\Entities;

final class ReputationEntity
{
    public function __construct(private int $points) {}

    public function handleReputation(string $type): void
    {
        match ($type) {
            'increment' => $this->incrementReputation(),
            'decrement' => $this->decrementReputation(),
        };
    }

    public function getBadge(): string
    {
        return match (true) {
            $this->points < 0 => 'Vacilão',
            $this->points === 0 => 'Sem informações',
            $this->points >= 0 && $this->points <= 5 => 'Ajudante',
            $this->points >= 5 && $this->points <= 10 => 'Lenda',
        };
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    private function incrementReputation(): void
    {
        $this->points++;
    }

    private function decrementReputation(): void
    {
        $this->points--;
    }
}
