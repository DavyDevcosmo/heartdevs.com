<x-profile::layout.guest :title="$profile->name">
    <main class="mx-auto max-w-5xl px-4 pb-16">
        <header class="mt-6">
            @if ($profile->coverUrl)
                <img src="{{ $profile->coverUrl }}" alt="" class="h-40 w-full rounded-3xl object-cover sm:h-56" />
            @else
                <div
                    aria-hidden="true"
                    class="from-primary via-primary/60 h-40 w-full rounded-3xl bg-gradient-to-br to-fuchsia-500/40 sm:h-56"
                ></div>
            @endif

            <div class="-mt-12 flex flex-col gap-4 px-2 sm:-mt-16 sm:flex-row sm:items-end sm:gap-6">
                <img
                    src="{{ $profile->avatarUrl }}"
                    alt="Foto de {{ $profile->name }}"
                    class="ring-elevation-surface bg-elevation-surface size-24 shrink-0 rounded-full object-cover ring-4 sm:size-32"
                />

                <div class="flex-1 pb-1">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                        <h1 class="text-text-high text-2xl font-bold sm:text-3xl">{{ $profile->name }}</h1>

                        @if ($profile->availableForProposals)
                            <span
                                class="bg-helper-success/15 text-helper-success inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold"
                            >
                                <span class="bg-helper-success size-1.5 rounded-full"></span>
                                Disponível para propostas
                            </span>
                        @endif
                    </div>

                    <p class="text-text-medium mt-1 text-sm">
                        {{ '@' . $profile->username }}

                        @if ($profile->nickname)
                            <span class="text-text-low">·</span>
                            {{ $profile->nickname }}
                        @endif
                    </p>

                    @if ($profile->headline)
                        <p class="text-text-high mt-3 text-base">{{ $profile->headline }}</p>
                    @endif

                    @if ($profile->currentPosition || $profile->location)
                        <div class="text-text-medium mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                            @if ($profile->currentPosition)
                                <span class="inline-flex items-center gap-1.5">
                                    <x-filament::icon icon="heroicon-m-briefcase" class="size-4" />
                                    {{ $profile->currentPosition }}

                                    @if ($profile->currentCompany)
                                        · {{ $profile->currentCompany }}
                                    @endif
                                </span>
                            @endif

                            @if ($profile->location)
                                <span class="inline-flex items-center gap-1.5">
                                    <x-filament::icon icon="heroicon-m-map-pin" class="size-4" />
                                    {{ $profile->location }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </header>
    </main>
</x-profile::layout.guest>
