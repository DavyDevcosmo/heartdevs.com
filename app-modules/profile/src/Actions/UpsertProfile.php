<?php

declare(strict_types=1);

namespace He4rt\Profile\Actions;

use Carbon\Carbon;
use He4rt\Profile\DTOs\UpsertProfileDTO;
use He4rt\Profile\Enums\SeniorityLevel;
use He4rt\Profile\Enums\SocialPlatform;
use He4rt\Profile\Models\Profile;
use InvalidArgumentException;

final class UpsertProfile
{
    public function handle(Profile $profile, UpsertProfileDTO $dto): Profile
    {
        $this->validate($dto);

        $profile->update(array_filter([
            'nickname' => $dto->nickname,
            'birthdate' => $dto->birthdate,
            'about' => $dto->about,
            'headline' => $dto->headline,
            'seniority_level' => $dto->seniorityLevel,
            'years_experience' => $dto->yearsExperience,
            'social_links' => $dto->socialLinks,
        ], fn (Carbon|string|SeniorityLevel|int|array|null $value) => !is_null($value)));

        return $profile->fresh();
    }

    private function validate(UpsertProfileDTO $dto): void
    {
        throw_if($dto->about !== null && mb_strlen($dto->about) > 500, InvalidArgumentException::class, 'O campo "sobre" não pode ultrapassar 500 caracteres.');

        throw_if($dto->headline !== null && mb_strlen($dto->headline) > 100, InvalidArgumentException::class, 'O título não pode ultrapassar 100 caracteres.');

        throw_if($dto->yearsExperience !== null && ($dto->yearsExperience < 0 || $dto->yearsExperience > 50), InvalidArgumentException::class, 'Os anos de experiência devem estar entre 0 e 50.');

        if ($dto->socialLinks !== null) {
            $validPlatforms = array_column(SocialPlatform::cases(), 'value');

            foreach ($dto->socialLinks as $key => $value) {
                throw_unless(in_array($key, $validPlatforms, true), InvalidArgumentException::class, sprintf('Plataforma social inválida: %s.', $key));

                throw_if(!is_string($value) || $value === '', InvalidArgumentException::class, sprintf('O valor para a plataforma %s deve ser uma string não vazia.', $key));
            }
        }
    }
}
