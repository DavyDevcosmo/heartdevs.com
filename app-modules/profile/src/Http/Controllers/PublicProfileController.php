<?php

declare(strict_types=1);

namespace He4rt\Profile\Http\Controllers;

use App\Http\Controllers\Controller;
use He4rt\Identity\User\Models\User;
use He4rt\Profile\Actions\BuildPublicProfile;
use He4rt\Profile\Queries\FindPublicProfileUser;
use He4rt\Profile\Seo\PublicProfileHead;
use Illuminate\Contracts\View\View;

final class PublicProfileController extends Controller
{
    public function __construct(
        private readonly BuildPublicProfile $buildPublicProfile,
        private readonly FindPublicProfileUser $findPublicProfileUser,
    ) {}

    public function __invoke(string $username): View
    {
        $user = $this->findPublicProfileUser->handle($username);

        abort_unless($user instanceof User, 404);

        $profile = $this->buildPublicProfile->handle($user);

        PublicProfileHead::apply($profile);

        return view('profile::public', ['profile' => $profile]);
    }
}
