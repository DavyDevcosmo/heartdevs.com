@props([
    'stats',
    'highlight' => null,
])

<section class="relative overflow-hidden px-6 pt-10 pb-8 lg:px-12 lg:pt-14">
    {{-- glow da marca: assinatura do portal, sob o conteúdo --}}
    <div
        aria-hidden="true"
        class="pointer-events-none absolute inset-x-0 -top-24 -z-10 mx-auto h-64 max-w-4xl rounded-full opacity-60 blur-3xl"
        style="background: radial-gradient(60% 120% at 50% 0%, color-mix(in oklab, var(--primary) 40%, transparent), transparent 70%)"
    ></div>

    <div class="mx-auto grid max-w-[1720px] gap-8 lg:grid-cols-[minmax(0,1fr)_420px] lg:items-start lg:gap-12">
        <div class="flex flex-col gap-5">
            <p class="text-text-medium flex items-center gap-3 font-mono text-xs tracking-[0.2em] uppercase">
                <span aria-hidden="true" class="bg-outline-low inline-block h-px w-8"></span>
                Learn in public · acervo no dev.to
            </p>

            <h1 class="text-text-high text-4xl leading-[1.08] font-bold tracking-tight lg:text-5xl">
                O que a <span class="text-primary">He4rt</span> escreveu, e quem escreveu.
            </h1>

            <p class="text-text-medium max-w-xl text-base leading-relaxed">
                Os artigos ao centro, quem escreve à direita, os temas a um clique na barra. Passe o mouse em qualquer
                entidade para ver o que se relaciona com ela nas outras.
            </p>

            <dl class="text-text-medium flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                @foreach ([['articles', 'artigos'], ['authors', 'autores'], ['topics', 'temas'], ['reactions', 'reações']] as [$key, $label])
                    <div class="flex items-baseline gap-2">
                        <dt class="sr-only">{{ $label }}</dt>
                        <dd class="text-text-high font-mono text-lg font-semibold tabular-nums">
                            {{ number_format($stats[$key], 0, ',', '.') }}
                        </dd>
                        <span aria-hidden="true">{{ $label }}</span>
                    </div>
                    @if (! $loop->last)
                        <span aria-hidden="true" class="text-text-low">·</span>
                    @endif
                @endforeach
            </dl>
        </div>

        @if ($highlight)
            <article
                class="border-outline-low bg-elevation-01dp relative flex flex-col gap-4 rounded-lg border p-4 transition-colors duration-300 hover:border-primary sm:flex-row lg:flex-col"
            >
                @if ($highlight->coverImage)
                    <img
                        src="{{ $highlight->coverImage }}"
                        alt=""
                        loading="lazy"
                        decoding="async"
                        class="aspect-video w-full rounded-sm object-cover sm:w-40 lg:w-full"
                    />
                @else
                    <x-portal::articles.cover-fallback class="aspect-video w-full rounded-sm sm:w-40 lg:w-full" />
                @endif

                <div class="flex min-w-0 flex-col gap-2">
                    <span
                        class="border-primary/32 bg-primary/5 text-text-high w-fit rounded-full border px-3 py-1 font-mono text-[0.65rem] tracking-wide"
                    >
                        ★ destaque · mais reagido dos últimos 12 meses
                    </span>

                    <h2 class="text-text-high line-clamp-3 text-lg leading-snug font-semibold">
                        <a
                            href="{{ $highlight->url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="after:absolute after:inset-0 focus-visible:outline-none"
                        >
                            {{ $highlight->title }}
                        </a>
                    </h2>

                    <div class="text-text-medium flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
                        <span class="flex items-center gap-2">
                            <img src="{{ $highlight->authorAvatar }}" alt="" loading="lazy" decoding="async" width="90" height="90" class="size-5 rounded-full" />
                            <span class="text-text-high font-medium">{{ $highlight->authorName }}</span>
                        </span>
                        <span class="font-mono tabular-nums">♥ {{ $highlight->reactions }}</span>
                        <span class="font-mono tabular-nums">{{ $highlight->readingMinutes }} min</span>
                        <span class="font-mono">{{ $highlight->publishedLabel() }}</span>
                    </div>
                </div>
            </article>
        @endif
    </div>
</section>
