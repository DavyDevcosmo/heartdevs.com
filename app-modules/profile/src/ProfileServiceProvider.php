<?php

declare(strict_types=1);

namespace He4rt\Profile;

use He4rt\Profile\Models\Profile;
use He4rt\Profile\Models\ProfileProject;
use He4rt\Profile\Models\ProfileSkill;
use He4rt\Profile\Models\WorkExperience;
use He4rt\Profile\Support\PublicProfileCache;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

final class ProfileServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'profile');

        Relation::morphMap([
            'profile' => Profile::class,
        ]);

        $this->forgetPublicProfileOnWrite();
    }

    private static function forget(?Profile $profile): null
    {
        if ($profile instanceof Profile) {
            PublicProfileCache::forget((string) $profile->user_id);
        }

        return null;
    }

    private function forgetPublicProfileOnWrite(): void
    {
        Profile::saved(static fn (Profile $profile): null => self::forget($profile));
        Profile::deleted(static fn (Profile $profile): null => self::forget($profile));

        WorkExperience::saved(static fn (WorkExperience $row): null => self::forget($row->profile));
        WorkExperience::deleted(static fn (WorkExperience $row): null => self::forget($row->profile));

        ProfileSkill::saved(static fn (ProfileSkill $row): null => self::forget($row->profile));
        ProfileSkill::deleted(static fn (ProfileSkill $row): null => self::forget($row->profile));

        ProfileProject::saved(static fn (ProfileProject $row): null => self::forget($row->profile));
        ProfileProject::deleted(static fn (ProfileProject $row): null => self::forget($row->profile));
    }
}
