<?php

declare(strict_types=1);

namespace He4rt\Profile\Http;

use Illuminate\Contracts\View\View;

final class ProfileController
{
    public function show(): View
    {
        return view('profile::public-profile');
    }
}
