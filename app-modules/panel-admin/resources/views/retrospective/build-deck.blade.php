@php
    use He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\InspectorMode;

    $selection = $this->selection();
    $mode = $selection->mode;
@endphp

<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
        {{-- Coluna 1: estrutura. Só seleciona e reordena; nada aqui salva texto. --}}
        <div class="xl:col-span-3">
            <x-filament::section>
                <x-slot name="heading">Estrutura</x-slot>
                <x-slot name="description">Clique para inspecionar. Use as setas para reordenar os blocos.</x-slot>

                <div class="space-y-1">
                    {{-- Capa --}}
                    <button
                        type="button"
                        wire:click="select('{{ InspectorMode::Cover->value }}')"
                        @class([
                            'flex w-full items-center gap-2 rounded-lg px-3 py-2 text-start text-sm font-medium transition',
                            'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' => $mode === InspectorMode::Cover,
                            'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5' => $mode !== InspectorMode::Cover,
                        ])
                    >
                        <x-filament::icon :icon="InspectorMode::Cover->getIcon()" class="h-4 w-4 shrink-0" />
                        {{ InspectorMode::Cover->getLabel() }}
                    </button>

                    {{-- Blocos de fonte, na ordem editorial --}}
                    @foreach ($this->blocks() as $index => $block)
                        <div class="rounded-lg border border-gray-200 dark:border-white/10">
                            <div class="flex items-center gap-1 px-1.5 py-1">
                                <button
                                    type="button"
                                    wire:click="select('{{ InspectorMode::Source->value }}:{{ $block->key }}')"
                                    @class([
                                        'flex min-w-0 flex-1 items-center gap-2 rounded-md px-1.5 py-1 text-start text-sm font-medium transition',
                                        'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' => $selection->selects(InspectorMode::Source, $block->key),
                                        'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5' => ! $selection->selects(InspectorMode::Source, $block->key),
                                    ])
                                >
                                    <span
                                        @class([
                                            'h-1.5 w-1.5 shrink-0 rounded-full',
                                            'bg-success-500' => $block->visible,
                                            'bg-gray-300 dark:bg-gray-600' => ! $block->visible,
                                        ])
                                        @if (! $block->visible) title="Oculta do deck" @endif
                                    ></span>
                                    <span class="truncate">{{ $block->label }}</span>
                                    @unless ($block->curatable)
                                        <x-filament::badge color="gray" size="xs">sem curadoria</x-filament::badge>
                                    @endunless
                                </button>

                                <x-filament::icon-button
                                    icon="heroicon-m-chevron-up"
                                    size="xs"
                                    color="gray"
                                    label="Subir {{ $block->label }}"
                                    :disabled="$index === 0"
                                    wire:click="moveSource('{{ $block->key }}', -1)"
                                />

                                <x-filament::icon-button
                                    icon="heroicon-m-chevron-down"
                                    size="xs"
                                    color="gray"
                                    label="Descer {{ $block->label }}"
                                    :disabled="$index === count($this->blocks()) - 1"
                                    wire:click="moveSource('{{ $block->key }}', 1)"
                                />
                            </div>

                            {{-- Chips de slide: on/off por KIND, não por instância. --}}
                            @if ($block->slides !== [])
                                <div class="flex flex-wrap gap-1 border-t border-gray-100 px-2 py-1.5 dark:border-white/5">
                                    @foreach ($block->slides as $slide)
                                        <button
                                            type="button"
                                            wire:click="select('{{ InspectorMode::Slide->value }}:{{ $slide->kind }}')"
                                            title="{{ $slide->hint ?? $slide->kind }}"
                                            @class([
                                                'rounded-md px-2 py-0.5 text-xs font-medium ring-1 transition',
                                                'bg-primary-50 text-primary-700 ring-primary-200 dark:bg-primary-500/10 dark:text-primary-300 dark:ring-primary-500/30' => $selection->selects(InspectorMode::Slide, $slide->kind),
                                                'text-gray-600 ring-gray-200 hover:bg-gray-50 dark:text-gray-400 dark:ring-white/10 dark:hover:bg-white/5' => ! $selection->selects(InspectorMode::Slide, $slide->kind) && $slide->visible,
                                                'text-gray-400 line-through ring-gray-200 hover:bg-gray-50 dark:text-gray-600 dark:ring-white/10 dark:hover:bg-white/5' => ! $selection->selects(InspectorMode::Slide, $slide->kind) && ! $slide->visible,
                                            ])
                                        >
                                            {{ $slide->label }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach

                    {{-- Fecho --}}
                    <button
                        type="button"
                        wire:click="select('{{ InspectorMode::Closing->value }}')"
                        @class([
                            'flex w-full items-center gap-2 rounded-lg px-3 py-2 text-start text-sm font-medium transition',
                            'bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300' => $mode === InspectorMode::Closing,
                            'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5' => $mode !== InspectorMode::Closing,
                        ])
                    >
                        <x-filament::icon :icon="InspectorMode::Closing->getIcon()" class="h-4 w-4 shrink-0" />
                        {{ InspectorMode::Closing->getLabel() }}
                    </button>
                </div>
            </x-filament::section>
        </div>

        {{--
            Coluna 2: preview. Iframe da MESMA rota pública de preview, que passa pelo
            mesmo ComposeDeck da página publicada — a única garantia de que o preview
            não mente é ser literalmente a mesma coisa (ADR-0002).
        --}}
        <div class="xl:col-span-6">
            <x-filament::section>
                <x-slot name="heading">Preview</x-slot>
                <x-slot name="description">Rascunho ao vivo pelo render path do deck publicado.</x-slot>

                <div
                    wire:key="preview-{{ $this->previewVersion }}"
                    x-data="{ loading: true }"
                    class="relative overflow-hidden rounded-xl border border-gray-200 dark:border-white/10"
                >
                    <div
                        x-show="loading"
                        x-transition.opacity
                        class="absolute inset-0 z-10 flex items-center justify-center bg-white/80 dark:bg-gray-900/80"
                    >
                        <x-filament::loading-indicator class="h-8 w-8 text-gray-400" />
                    </div>

                    <iframe
                        src="{{ $this->previewUrl() }}"
                        title="Preview do deck"
                        loading="lazy"
                        x-on:load="loading = false"
                        class="h-[75vh] w-full bg-white dark:bg-gray-950"
                    ></iframe>
                </div>
            </x-filament::section>
        </div>

        {{-- Coluna 3: inspector. Contextual, quatro modos, escreve onde a Fase 2 escrevia. --}}
        <div class="xl:col-span-3">
            <div class="mb-3 flex items-start gap-2">
                <x-filament::icon :icon="$mode->getIcon()" class="mt-0.5 h-5 w-5 shrink-0 text-gray-400" />
                <div>
                    <h3 class="text-sm font-semibold text-gray-950 dark:text-white">{{ $mode->getLabel() }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $mode->getDescription() }}</p>
                </div>
            </div>

            {{ $this->form }}
        </div>
    </div>
</x-filament-panels::page>
