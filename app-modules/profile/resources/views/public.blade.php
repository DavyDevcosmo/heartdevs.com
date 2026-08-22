@php
    // Everything below the fold is optional, so the share card falls back down a
    // ladder: headline, then current role, then the bio, then a plain sentence.
    $metaDescription = collect([
        $profile->headline,
        $profile->currentPosition && $profile->currentCompany
            ? $profile->currentPosition . ' · ' . $profile->currentCompany
            : $profile->currentPosition,
        $profile->about,
    ])->filter()->first() ?? $profile->name . ' na comunidade He4rt Developers.';

    $facts = array_filter([
        'Senioridade' => $profile->seniority,
        'Experiência' => $profile->yearsExperience
            ? $profile->yearsExperience . ($profile->yearsExperience === 1 ? ' ano' : ' anos')
            : null,
        'Início' => $profile->startAvailability,
    ]);

    $workPreferences = array_filter([
        $profile->openToRemote ? 'Aberto a remoto' : null,
        $profile->willingToRelocate ? 'Disposto a mudar de cidade' : null,
        ...$profile->employmentTypes,
    ]);

    $links = [...$profile->socialLinks, ...$profile->connectedAccounts];

    // Iniciais do avatar sem foto. Só palavras que começam com letra entram, senão
    // um nome como "Écio Gonçalves 🇧🇷" renderiza a bandeira como se fosse inicial.
    $initials = Str::of($profile->name)
        ->squish()
        ->explode(' ')
        ->filter(fn (string $word): bool => preg_match('/^\p{L}/u', $word) === 1)
        ->take(2)
        ->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))
        ->implode('');

    $initials = $initials !== '' ? $initials : Str::upper(Str::substr($profile->username, 0, 1));

    // Todo cadastro nasce com Profile, então perfil vazio é o estado padrão: sem
    // nada pra contar, a página vira coluna única em vez de duas com metade vazia.
    $hasContent = $profile->about || $profile->skills !== [] || $profile->experiences !== [] || $facts !== [];
@endphp

<x-profile::layout.guest
    :title="$profile->name"
    {{-- 157 + the ellipsis: what a share card shows before it cuts the text itself. --}}
    :description="Str::limit($metaDescription, 157)"
    :image="$profile->coverUrl ?? $profile->avatarUrl"
    type="profile"
>
    <main @class(['mx-auto px-4 pb-24', 'max-w-6xl' => $hasContent, 'max-w-2xl' => ! $hasContent])>
        @if ($profile->coverUrl)
            <img src="{{ $profile->coverUrl }}" alt="" class="mt-6 h-32 w-full rounded-3xl object-cover sm:h-44" />
        @else
            <div
                aria-hidden="true"
                class="from-primary via-primary/60 mt-6 h-32 w-full rounded-3xl bg-gradient-to-br to-fuchsia-500/40 sm:h-44"
            ></div>
        @endif

        <div @class(['grid gap-10 lg:gap-14', 'lg:grid-cols-[320px_1fr]' => $hasContent])>
            {{-- Rail de identidade: fica visível enquanto o currículo rola ao lado. --}}
            {{-- min-w-0: item de grid nasce com min-width auto e se recusa a encolher
                 abaixo do próprio conteúdo. Sem isso, um handle ou headline longo estica
                 a trilha da grid e a página inteira rola de lado no mobile. --}}
            <aside class="min-w-0 lg:sticky lg:top-8 lg:self-start">
                @if ($profile->avatarUrl)
                    <img
                        src="{{ $profile->avatarUrl }}"
                        alt="Foto de {{ $profile->name }}"
                        class="bg-elevation-surface -mt-14 size-28 rounded-full object-cover"
                        style="box-shadow: 0 0 0 3px var(--elevation-surface), 0 0 0 6px rgb(120 43 241 / 0.45)"
                    />
                @else
                    {{-- Sem foto: as iniciais do nome, que já está logo abaixo — daí o aria-hidden. --}}
                    <div
                        aria-hidden="true"
                        class="bg-elevation-01dp text-text-high -mt-14 flex size-28 items-center justify-center rounded-full text-3xl font-bold"
                        style="box-shadow: 0 0 0 3px var(--elevation-surface), 0 0 0 6px rgb(120 43 241 / 0.45)"
                    >
                        {{ $initials }}
                    </div>
                @endif

                <h1 class="text-text-high mt-4 text-2xl font-bold">{{ $profile->name }}</h1>

                <p class="text-text-medium mt-0.5 text-sm">
                    {{ '@' . $profile->username }}

                    @if ($profile->nickname)
                        <span class="text-text-low">·</span>
                        {{ $profile->nickname }}
                    @endif
                </p>

                @if ($profile->headline)
                    <p class="text-text-high mt-3 text-sm leading-relaxed">{{ $profile->headline }}</p>
                @endif

                @if ($profile->availableForProposals)
                    <span
                        class="bg-helper-success/15 text-helper-success mt-4 inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold"
                    >
                        <span class="bg-helper-success size-1.5 rounded-full"></span>
                        Disponível para propostas
                    </span>
                @endif

                @if ($profile->currentPosition || $profile->location)
                    <ul class="text-text-medium mt-5 space-y-2 text-sm">
                        @if ($profile->currentPosition)
                            <li class="flex items-center gap-2">
                                <x-filament::icon icon="heroicon-m-briefcase" class="text-icon-medium size-4 shrink-0" />
                                <span>
                                    {{ $profile->currentPosition }}

                                    @if ($profile->currentCompany)
                                        · {{ $profile->currentCompany }}
                                    @endif
                                </span>
                            </li>
                        @endif

                        @if ($profile->location)
                            <li class="flex items-center gap-2">
                                <x-filament::icon icon="heroicon-m-map-pin" class="text-icon-medium size-4 shrink-0" />
                                <span>{{ $profile->location }}</span>
                            </li>
                        @endif
                    </ul>
                @endif

                @if ($workPreferences !== [])
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($workPreferences as $preference)
                            <li
                                class="bg-primary/5 border-primary/32 text-text-high rounded-xl border px-2.5 py-1.5 text-xs font-medium"
                            >
                                {{ $preference }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($links !== [])
                    <div class="border-outline-low mt-6 border-t pt-6">
                        <h2 class="text-text-high text-sm font-semibold">Links</h2>

                        {{-- Lista vertical, não nuvem de pílulas: escaneia melhor num rail estreito. --}}
                        <ul class="mt-3 space-y-1">
                            @foreach ($links as $link)
                                <li>
                                    @if ($link->url)
                                        <a
                                            href="{{ $link->url }}"
                                            target="_blank"
                                            rel="noopener noreferrer me"
                                            class="text-text-medium hover:bg-elevation-01dp hover:text-text-high -mx-2 flex items-center gap-2.5 rounded-lg px-2 py-1.5 text-sm transition-colors"
                                        >
                                            <x-filament::icon :icon="$link->icon" class="size-4 shrink-0" />
                                            <span class="truncate">{{ $link->handle }}</span>
                                        </a>
                                    @else
                                        {{-- Discord has no public profile page: handle only, no link. --}}
                                        <span
                                            class="text-text-medium -mx-2 flex items-center gap-2.5 rounded-lg px-2 py-1.5 text-sm"
                                            title="{{ $link->label }}"
                                        >
                                            <x-filament::icon :icon="$link->icon" class="size-4 shrink-0" />
                                            <span class="truncate">{{ $link->handle }}</span>
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($profile->level || $profile->badges !== [])
                    <div class="border-outline-low mt-6 border-t pt-6">
                        <h2 class="text-text-high text-sm font-semibold">Comunidade</h2>

                        @if ($profile->level)
                            <p class="text-text-high mt-3 text-sm font-semibold">Nível {{ $profile->level }}</p>

                            <div class="bg-elevation-02dp mt-2 h-1.5 w-full overflow-hidden rounded-full">
                                <div
                                    class="bg-primary h-full rounded-full"
                                    style="width: {{ $profile->levelProgress }}%"
                                ></div>
                            </div>

                            <p class="text-text-low mt-2 text-xs">
                                {{ number_format((float) $profile->experience, 0, ',', '.') }} XP
                                @if ($profile->experienceToNextLevel)
                                    · {{ number_format((float) $profile->experienceToNextLevel, 0, ',', '.') }}
                                    para o próximo nível
                                @endif
                            </p>
                        @endif

                        @if ($profile->badges !== [])
                            {{-- Name and description only: the redeem code stays out of the DTO. --}}
                            {{-- Nome e descrição visíveis, não em tooltip: sem eles a seção
                                 vira uma fileira de bolinhas que parece decoração. --}}
                            <ul class="mt-4 space-y-2">
                                @foreach ($profile->badges as $badge)
                                    <li
                                        class="border-outline-low bg-elevation-01dp flex items-center gap-3 rounded-xl border p-3"
                                    >
                                        @if ($badge->imageUrl)
                                            {{-- alt vazio: o nome já é o texto ao lado. --}}
                                            <img
                                                src="{{ $badge->imageUrl }}"
                                                alt=""
                                                class="size-9 shrink-0 rounded-lg object-contain"
                                            />
                                        @else
                                            <span
                                                aria-hidden="true"
                                                class="bg-elevation-02dp text-text-medium flex size-9 shrink-0 items-center justify-center rounded-lg text-xs font-bold"
                                            >
                                                {{ mb_substr($badge->name, 0, 1) }}
                                            </span>
                                        @endif

                                        <div class="min-w-0">
                                            <h3 class="text-text-high text-sm font-medium">{{ $badge->name }}</h3>
                                            <p class="text-text-medium mt-0.5 text-xs">{{ $badge->description }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if ($profile->memberFor)
                            <p class="border-outline-low text-text-low mt-4 border-t pt-3 text-xs">
                                Membro há {{ $profile->memberFor }}
                            </p>
                        @endif
                    </div>
                @endif
            </aside>

            @if ($hasContent)
                <div class="pt-8 lg:pt-10">
                    @if ($profile->about)
                        <section>
                            <h2 class="text-text-high text-lg font-semibold">Sobre</h2>
                            <p class="text-text-medium mt-3 max-w-prose leading-relaxed whitespace-pre-line">
                                {{ $profile->about }}
                            </p>
                        </section>
                    @endif

                    @if ($facts !== [])
                        <dl class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach ($facts as $label => $value)
                                <div class="border-outline-low rounded-2xl border px-4 py-3">
                                    <dt class="text-text-low text-xs font-medium tracking-wide uppercase">
                                        {{ $label }}
                                    </dt>
                                    <dd class="text-text-high mt-1 font-semibold">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif

                    @if ($profile->skills !== [])
                        <section class="mt-12">
                            <h2 class="text-text-high text-lg font-semibold">Skills</h2>

                            <ul class="mt-4 flex flex-wrap gap-2">
                                @foreach ($profile->skills as $skill)
                                    <li class="border-outline-low flex items-baseline gap-2 rounded-xl border px-3 py-2">
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
                        <section class="mt-12">
                            <h2 class="text-text-high text-lg font-semibold">Experiência profissional</h2>

                            <ol class="border-outline-low mt-6 space-y-8 border-l pl-6">
                                @foreach ($profile->experiences as $experience)
                                    <li class="relative">
                                        <span
                                            class="bg-primary ring-elevation-surface absolute top-1.5 -left-[1.8rem] size-2.5 rounded-full ring-4"
                                        ></span>

                                        <div class="flex flex-wrap items-baseline gap-x-2">
                                            <h3 class="text-text-high font-semibold">{{ $experience->position }}</h3>
                                            <span class="text-text-medium text-sm">· {{ $experience->company }}</span>

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
                                            <p
                                                class="text-text-medium mt-2 max-w-prose text-sm leading-relaxed whitespace-pre-line"
                                            >
                                                {{ $experience->description }}
                                            </p>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        </section>
                    @endif
                </div>
            @endif
        </div>
    </main>
</x-profile::layout.guest>
