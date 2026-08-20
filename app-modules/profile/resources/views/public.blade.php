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

        @php
            $facts = array_filter([
                'Senioridade' => $profile->seniority,
                'Experiência' => $profile->yearsExperience ? $profile->yearsExperience . ' anos' : null,
                'Idade' => $profile->age ? $profile->age . ' anos' : null,
                'Início' => $profile->startAvailability,
            ]);

            $workPreferences = array_filter([
                $profile->openToRemote ? 'Aberto a remoto' : null,
                $profile->willingToRelocate ? 'Disposto a mudar de cidade' : null,
                ...$profile->employmentTypes,
            ]);
        @endphp

        @if ($profile->about || $facts !== [] || $workPreferences !== [])
            <section class="mt-10 px-2">
                <h2 class="text-text-high text-lg font-semibold">Sobre</h2>

                @if ($profile->about)
                    <p class="text-text-medium mt-3 leading-relaxed whitespace-pre-line">{{ $profile->about }}</p>
                @endif

                @if ($facts !== [])
                    <dl class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                        @foreach ($facts as $label => $value)
                            <div class="bg-elevation-01dp rounded-2xl px-4 py-3">
                                <dt class="text-text-low text-xs font-medium tracking-wide uppercase">{{ $label }}</dt>
                                <dd class="text-text-high mt-1 font-semibold">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @if ($workPreferences !== [])
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($workPreferences as $preference)
                            <li
                                class="text-text-medium ring-outline-low rounded-full px-3 py-1 text-sm ring-1 ring-inset"
                            >
                                {{ $preference }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif

        @php ($links = [...$profile->socialLinks, ...$profile->connectedAccounts])

        @if ($links !== [])
            <section class="mt-10 px-2">
                <h2 class="text-text-high text-lg font-semibold">Links</h2>

                <ul class="mt-4 flex flex-wrap gap-3">
                    @foreach ($links as $link)
                        <li>
                            @if ($link->url)
                                <a
                                    href="{{ $link->url }}"
                                    target="_blank"
                                    rel="noopener noreferrer me"
                                    class="text-text-high ring-outline-low hover:bg-elevation-01dp inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm ring-1 ring-inset transition-colors"
                                >
                                    <x-filament::icon :icon="$link->icon" class="size-4" />
                                    <span>{{ $link->handle }}</span>
                                </a>
                            @else
                                {{-- Discord has no public profile page: handle only, no link. --}}
                                <span
                                    class="text-text-medium ring-outline-low inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm ring-1 ring-inset"
                                    title="{{ $link->label }}"
                                >
                                    <x-filament::icon :icon="$link->icon" class="size-4" />
                                    <span>{{ $link->handle }}</span>
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($profile->skills !== [])
            <section class="mt-10 px-2">
                <h2 class="text-text-high text-lg font-semibold">Skills</h2>

                <ul class="mt-4 flex flex-wrap gap-2">
                    @foreach ($profile->skills as $skill)
                        <li
                            class="bg-elevation-01dp ring-outline-low flex items-baseline gap-2 rounded-xl px-3 py-2 ring-1 ring-inset"
                        >
                            <span class="text-text-high text-sm font-semibold">{{ $skill->name }}</span>
                            <span class="text-text-medium text-xs">{{ $skill->proficiency }}</span>

                            @if ($skill->yearsExperience)
                                <span class="text-text-low text-xs">
                                    {{ $skill->yearsExperience }}{{ $skill->yearsExperience === 1 ? ' ano' : ' anos' }}
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($profile->experiences !== [])
            <section class="mt-10 px-2">
                <h2 class="text-text-high text-lg font-semibold">Experiência profissional</h2>

                <ol class="mt-4 space-y-6">
                    @foreach ($profile->experiences as $experience)
                        <li class="border-outline-low border-l-2 pl-4">
                            <div class="flex flex-wrap items-baseline gap-x-2">
                                <h3 class="text-text-high font-semibold">{{ $experience->position }}</h3>
                                <span class="text-text-medium">· {{ $experience->company }}</span>

                                @if ($experience->isCurrent)
                                    <span
                                        class="bg-helper-success/15 text-helper-success rounded-full px-2 py-0.5 text-xs font-semibold"
                                    >
                                        Atual
                                    </span>
                                @endif
                            </div>

                            <p class="text-text-low mt-1 text-sm">
                                {{ $experience->period }}

                                @if ($experience->duration)
                                    · {{ $experience->duration }}
                                @endif
                            </p>

                            @if ($experience->description)
                                <p class="text-text-medium mt-2 text-sm leading-relaxed whitespace-pre-line">
                                    {{ $experience->description }}
                                </p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif
    </main>
</x-profile::layout.guest>
