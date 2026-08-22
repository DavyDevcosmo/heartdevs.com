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
