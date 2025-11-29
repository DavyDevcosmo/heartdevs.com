@props([
    'event',
    'speakers' => $event->speakers->where('name',
    '!=',
    $event->slug),
])

@php
    $fodases = [
        [
            'avatar' => 'https://i.imgur.com/1mo38XW.jpeg',
            'name' => 'Eduardo Vogel',
            'role' => 'Coordenador de negócios',
            'linkedin_url' => 'https://www.linkedin.com/in/eduardovogel11/',
            'description' => 'Atuante no setor de tecnologia há 10 anos, formado em Administração pelo Eckerd College em St. Petersburg, Flórida, e vencedor do hackathon da Câmara dos Vereadores de São Paulo.',
        ],
        [
            'avatar' => 'https://i.imgur.com/n7ZlSZ2.png',
            'name' => 'Filipe Augusto',
            'role' => 'CEO 3Pontos',
            'linkedin_url' => 'https://www.linkedin.com/in/filipe-augustos',
            'description' => 'Empreendedor nos setores financeiro e de tecnologia, atuando na criação, expansão e gestão de negócios focados em educação financeira.',
        ],
        [
            'avatar' => 'https://i.imgur.com/lxh4vKh.jpeg',
            'name' => 'Joy Jesus',
            'role' => 'CMO 3Pontos',
            'linkedin_url' => 'https://www.linkedin.com/in/joy-jesus-b14721221',
            'description' => 'CEO e fundador da Start Digital, especializado em consultoria de marketing e posicionamento de marca.',
        ],
        [
            'avatar' => 'https://i.imgur.com/UpJpsvk.jpeg',
            'name' => 'Juliano Kimura',
            'role' => 'CMO at Cursed Stone Game',
            'linkedin_url' => 'https://www.linkedin.com/in/fernandokimura',
            'description' => 'Palestrante e consultor em inovação digital, especialista em redes sociais, premiado no setor e experiente na gestão de equipes e projetos criativos de alto impacto.',
        ],
    ];
@endphp

<section class="hp-section relative" id="speakers">
    <div
        class="absolute bottom-0 left-0 z-1 flex origin-top rotate-90 justify-start sm:-translate-x-[20%] sm:translate-y-32 lg:-translate-x-[5%] lg:translate-y-24 lg:rotate-0 xl:translate-y-0"
    >
        <img
            src="{{ asset('images/3pontos/logo-chain.png') }}"
            alt=""
            class="hidden h-auto w-full object-contain sm:block sm:max-w-[60%]"
        />
    </div>

    <div class="hp-container relative z-10">
        <div>
            <x-he4rt::headline align="center">
                <x-slot:badge>
                    <x-he4rt::section-title>Palestrantes</x-he4rt::section-title>
                </x-slot>
                <x-slot:title>Palestrantes do evento</x-slot>
            </x-he4rt::headline>
        </div>

        <div
            x-data="{ visible: false }"
            x-intersect.once="visible = true"
            class="grid max-w-7xl grid-cols-1 gap-6 sm:grid-cols-2 sm:gap-8 lg:grid-cols-3"
        >
            @forelse ($speakers as $speaker)
                <x-he4rt::animate-block>
                    <x-he4rt::card
                        class="bg-elevation-01dp/32 group hover:border-b-outline-light relative h-[17rem] gap-2 overflow-hidden border-b-8 transition-all duration-500 hover:gap-1 lg:gap-4"
                    >
                        <x-slot:header class="border-none pb-0">
                            <div class="relative w-full transition-all duration-500 ease-in-out">
                                <div
                                    class="absolute top-0 scale-75 transition-all duration-500 ease-in-out group-hover:left-0 group-hover:scale-75 lg:scale-100"
                                >
                                    <x-he4rt::avatar size="2xl" src="{{ $speaker->getFirstMediaUrl('avatar') }}" />
                                </div>

                                <div
                                    class="flex min-h-20 scale-90 flex-col justify-center gap-y-2 pt-0 pl-20 text-left transition-all duration-500 ease-in-out group-hover:scale-90 group-hover:justify-center group-hover:pt-0 group-hover:pl-20 lg:scale-100 lg:justify-start lg:pt-24 lg:pl-0"
                                >
                                    <x-he4rt::heading level="3" size="xs">
                                        {{ $speaker->name }}
                                    </x-he4rt::heading>

                                    <x-he4rt::text class="transition-opacity duration-300 group-hover:opacity-80">
                                        {{ $speaker->talks->first()->field_type }}
                                    </x-he4rt::text>
                                </div>
                            </div>
                        </x-slot>

                        <div class="custom-scrollbar h-full overflow-hidden transition-all duration-500 ease-in-out">
                            <x-he4rt::text
                                class="line-clamp-1 hidden transition-all duration-500 group-hover:hidden lg:block"
                            >
                                Ver Mais
                            </x-he4rt::text>
                            <x-he4rt::text
                                class="opacity-100 transition-all duration-500 group-hover:line-clamp-none group-hover:opacity-100 lg:line-clamp-1 lg:opacity-0"
                            >
                                {{ $speaker->talks->first()->description }}
                            </x-he4rt::text>
                        </div>

                        <div class="flex gap-x-8 group-hover:flex lg:hidden">
                            <x-he4rt::icon
                                rel="noopener noreferrer"
                                target="_blank"
                                size="sm"
                                :href="$speaker->information->linkedin_url ?? 'https://www.linkedin.com/company/3pontos3/'"
                                icon="fab-linkedin"
                                class="text-icon-light h-full border-none bg-transparent p-0"
                            />
                            <x-he4rt::icon
                                rel="noopener noreferrer"
                                target="_blank"
                                size="sm"
                                :href="$speaker->information->github_url ?? 'https://github.com/3pontos-tech'"
                                icon="fab-github"
                                class="text-icon-light h-full border-none bg-transparent p-0"
                            />
                        </div>
                    </x-he4rt::card>
                </x-he4rt::animate-block>
            @empty
                <p>There is no Speaker Yet.</p>
            @endforelse

            @foreach ($fodases as $fodase)
                <x-he4rt::animate-block>
                    <x-he4rt::card
                        class="bg-elevation-01dp/32 group hover:border-b-outline-light relative h-[17rem] gap-2 overflow-hidden border-b-8 transition-all duration-500 hover:gap-2 lg:gap-4"
                    >
                        <x-slot:header class="border-none pb-0">
                            <div class="relative w-full transition-all duration-500 ease-in-out">
                                <div
                                    class="absolute top-0 scale-75 transition-all duration-500 ease-in-out group-hover:left-0 group-hover:scale-75 lg:scale-100"
                                >
                                    <x-he4rt::avatar size="2xl" src="{{ $fodase['avatar'] }}" />
                                </div>

                                <div
                                    class="flex min-h-20 scale-90 flex-col justify-center gap-y-2 pt-0 pl-20 text-left transition-all duration-500 ease-in-out group-hover:scale-90 group-hover:justify-center group-hover:pt-0 group-hover:pl-20 lg:scale-100 lg:justify-start lg:pt-24 lg:pl-0"
                                >
                                    <x-he4rt::heading level="3" size="xs">
                                        {{ $fodase['name'] }}
                                    </x-he4rt::heading>

                                    <x-he4rt::text class="transition-opacity duration-300 group-hover:opacity-80">
                                        {{ $fodase['role'] }}
                                    </x-he4rt::text>
                                </div>
                            </div>
                        </x-slot>

                        <div class="custom-scrollbar h-full overflow-hidden transition-all duration-500 ease-in-out">
                            <x-he4rt::text
                                class="line-clamp-1 hidden transition-all duration-500 group-hover:hidden lg:block"
                            >
                                Ver Mais
                            </x-he4rt::text>
                            <x-he4rt::text
                                class="opacity-100 transition-all duration-500 group-hover:line-clamp-none group-hover:opacity-100 lg:line-clamp-1 lg:opacity-0"
                            >
                                {{ $fodase['description'] }}
                            </x-he4rt::text>
                        </div>

                        <div class="flex gap-x-8 group-hover:flex lg:hidden">
                            <x-he4rt::icon
                                rel="noopener noreferrer"
                                target="_blank"
                                size="sm"
                                :href="$fodase['linkedin_url'] ?? 'https://www.linkedin.com/company/3pontos3/'"
                                icon="fab-linkedin"
                                class="text-icon-light h-full border-none bg-transparent p-0"
                            />
                        </div>
                    </x-he4rt::card>
                </x-he4rt::animate-block>
            @endforeach
        </div>
    </div>
</section>
