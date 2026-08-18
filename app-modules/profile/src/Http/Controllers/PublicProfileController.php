<?php

declare(strict_types=1);

namespace He4rt\Profile\Http\Controllers;

use App\Http\Controllers\Controller;
use He4rt\Identity\User\Models\User;
use He4rt\Profile\Actions\BuildPublicProfile;
use Illuminate\Contracts\View\View;

final class PublicProfileController extends Controller
{
    public function __construct(
        private readonly BuildPublicProfile $buildPublicProfile,
    ) {}

    public function __invoke(string $username): View
    {
        // A banned user has no public profile at all. A suspended one still
        // renders: the suspension limits what they can do, not who they are.
        $user = User::query()
            ->where('username', $username)
            ->whereNull('banned_at')
            ->first();

        abort_unless($user instanceof User, 404);

        return view('profile::public', [
            'profile' => $this->buildPublicProfile->handle($user),
        ]);
    }
}
