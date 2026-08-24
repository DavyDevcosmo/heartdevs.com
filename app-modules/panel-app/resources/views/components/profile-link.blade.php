@props (['user' => null])

@php
    $isLinkable = $user !== null && $user->banned_at === null && filled($user->username);
@endphp

@if ($isLinkable)
    <a href="{{ route('profile.public', $user->username) }}" {{ $attributes }}>
        {{ $slot }}
    </a>
@else
    <span {{ $attributes }}>{{ $slot }}</span>
@endif
