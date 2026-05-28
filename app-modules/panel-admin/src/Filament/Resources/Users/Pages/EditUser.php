<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Filament\Resources\Users\Pages;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use He4rt\PanelAdmin\Filament\Resources\Users\UserResource;
use He4rt\Profile\Actions\ToggleAvailability;
use He4rt\Profile\Actions\UpsertProfile;
use He4rt\Profile\DTOs\UpsertProfileDTO;
use He4rt\Profile\Enums\SocialPlatform;
use He4rt\Profile\Enums\StartAvailability;
use He4rt\Profile\Models\Profile;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $profile = $this->getProfile();

        $data['profile'] = [
            'nickname' => $profile->nickname,
            'birthdate' => $profile->birthdate?->format('Y-m-d'),
            'headline' => $profile->headline,
            'seniority_level' => $profile->seniority_level,
            'years_experience' => $profile->years_experience,
            'about' => $profile->about,
            'available_for_proposals' => $profile->available_for_proposals,
            'start_availability' => $profile->start_availability,
            'social_links' => $this->socialLinksToRepeater($profile->social_links),
        ];

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $profile = $this->getProfile();
        $profileData = $data['profile'] ?? [];

        $socialLinks = $this->repeaterToSocialLinks($profileData['social_links'] ?? []);

        $dto = UpsertProfileDTO::fromArray([
            'nickname' => $profileData['nickname'] ?? null,
            'birthdate' => $profileData['birthdate'] ?? null,
            'about' => $profileData['about'] ?? null,
            'headline' => $profileData['headline'] ?? null,
            'seniority_level' => $profileData['seniority_level'] ?? null,
            'years_experience' => $profileData['years_experience'] ?? null,
            'social_links' => $socialLinks !== [] ? $socialLinks : null,
        ]);

        resolve(UpsertProfile::class)->handle($profile, $dto);

        $available = (bool) ($profileData['available_for_proposals'] ?? false);
        $rawStartAvailability = $profileData['start_availability'] ?? null;
        $startAvailability = match (true) {
            $rawStartAvailability instanceof StartAvailability => $rawStartAvailability,
            is_string($rawStartAvailability) => StartAvailability::from($rawStartAvailability),
            $available => StartAvailability::Negotiable,
            default => null,
        };

        resolve(ToggleAvailability::class)->handle($profile, $available, $startAvailability);

        Notification::make()
            ->success()
            ->title('Perfil atualizado com sucesso!')
            ->send();

        return $record;
    }

    private function getProfile(): Profile
    {
        $tenantId = Filament::getTenant()?->getKey();
        abort_unless($tenantId, 403);

        return Profile::query()->firstOrCreate([
            'user_id' => $this->record->getKey(),
            'tenant_id' => $tenantId,
        ]);
    }

    /**
     * @param  array<string, string>|null  $socialLinks
     * @return list<array{platform: string, handle: string}>
     */
    private function socialLinksToRepeater(?array $socialLinks): array
    {
        if ($socialLinks === null) {
            return [];
        }

        return collect($socialLinks)
            ->map(fn ($handle, $platform) => ['platform' => $platform, 'handle' => $handle])
            ->values()
            ->all();
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $repeaterData
     * @return array<string, string>
     */
    private function repeaterToSocialLinks(array $repeaterData): array
    {
        $links = [];

        foreach ($repeaterData as $item) {
            $platform = $item['platform'] ?? null;
            $handle = $item['handle'] ?? null;

            if (filled($platform) && filled($handle)) {
                $key = $platform instanceof SocialPlatform ? $platform->value : (string) $platform;
                $links[$key] = (string) $handle;
            }
        }

        return $links;
    }
}
