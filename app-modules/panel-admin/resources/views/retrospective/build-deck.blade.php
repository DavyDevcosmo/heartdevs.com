@php
    use He4rt\Community\Retrospective\Enums\RetrospectiveStatus;
    use He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\InspectorMode;

    $selection = $this->selection();
    $mode = $selection->mode;
    $record = $this->getRetrospective();
    $status = $record->status;
@endphp

<x-filament-panels::page>
    {{-- O design system do deck, o mesmo do portal. Todo ele é escopado sob
         `.retro`, então entra no painel sem alcançar nada do Filament. --}}
    @vite(['app-modules/portal/resources/css/retrospective.css'])

    {{--
        Status da edição + aviso de drift. Enquanto o job congela o snapshot o poll
        relê o registro, para "Publicando" virar "Publicada" sem recarregar na mão.
    --}}
    <div
        class="flex flex-wrap items-center gap-2"
        @if ($status === RetrospectiveStatus::Publishing) wire:poll.3s="refreshStatus" @endif
    >
        <x-filament::badge :color="$status->getColor()" :icon="$status->getIcon()">
            {{ $status->getLabel() }}
        </x-filament::badge>

        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $status->getDescription() }}</span>

        @if ($record->needsRepublish())
            <x-filament::badge color="warning" icon="heroicon-m-exclamation-triangle">
                Republique: exclusion mudou depois de publicar
            </x-filament::badge>
        @endif
    </div>
    {{--
        Duas colunas: o deck fica com todo o espaço que sobra e o inspector com a
        largura FIXA de que um formulário precisa — ela não cresce com o monitor.
        A estrutura saiu daqui para o rodapé: ela é uma sequência, e sequência se
        lê deitada.
    --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_18rem]">
        {{--
            Coluna 1: preview. Passa pelo MESMO ComposeDeck e pelas MESMAS partials
            da página publicada — a única garantia de que o preview não mente é ser
            literalmente a mesma coisa (ADR-0002).
        --}}
        <div class="min-w-0">
            <x-filament::section>
                <x-slot name="heading">Preview</x-slot>
                <x-slot name="description">Prévia ao vivo pelo mesmo render path do deck publicado.</x-slot>

                {{--
                    O arquivo do que está selecionado, para copiar direto no editor.
                    Encurta o caminho entre ver algo torto no preview e abrir o blade
                    certo — a convenção kind -> partial mora no portal (SlideView).
                --}}
                @if ($path = $this->viewPath())
                    <x-slot name="afterHeader">
                        <div
                            x-data="{ copied: false }"
                            class="flex min-w-0 items-center gap-1.5"
                        >
                            {{-- Renderizado no servidor, não via x-text: o caminho é a
                                 informação, e ela não pode depender do Alpine ter subido. --}}
                            <code
                                x-ref="path"
                                title="{{ $path }}"
                                class="min-w-0 truncate rounded-md bg-gray-50 px-2 py-1 font-mono text-xs text-gray-600 dark:bg-white/5 dark:text-gray-400"
                            >{{ $path }}</code>

                            <x-filament::icon-button
                                icon="heroicon-m-clipboard-document"
                                x-show="! copied"
                                size="sm"
                                color="gray"
                                label="Copiar caminho da view"
                                x-on:click="
                                    navigator.clipboard.writeText($refs.path.textContent.trim()).then(() => {
                                        copied = true
                                        setTimeout(() => (copied = false), 1500)
                                    })
                                "
                            />

                            <x-filament::icon-button
                                icon="heroicon-m-clipboard-document-check"
                                x-show="copied"
                                x-cloak
                                size="sm"
                                color="success"
                                label="Caminho copiado"
                            />
                        </div>
                    </x-slot>
                @endif

                {{--
                    O deck de verdade, no mesmo DOM do builder: mesmas partials e
                    mesmo ComposeDeck da página pública, sem iframe no meio. O host
                    (.retro-embed) vira o containing block que faz o deck — `fixed`
                    quando ELE é a página — caber nesta coluna.

                    Quem manda o preview pular para o slide selecionado é o servidor,
                    via $this->js() depois de cada seleção: o índice muda a cada
                    render, e um x-data só seria avaliado na primeira vez.
                --}}
                {{--
                    Island: selecionar na estrutura ou digitar no inspector re-renderiza
                    o resto da página, mas NÃO este fragmento — o morph reescreveria o
                    deck com HTML sem as classes que o Alpine aplicou (slide ativo
                    sumia) e cada clique pagaria uma recoleta das fontes. O deck só
                    re-renderiza quando algo foi salvo (renderIsland em refreshPreview).

                    O deck avisa quando o operador navega por dentro dele; a estrutura,
                    o inspector e o caminho da view seguem o slide.
                --}}
                {{--
                    O listener fica FORA do island de propósito: uma ação disparada de
                    um elemento de dentro vira uma chamada escopada ao island e o
                    servidor re-morfaria o deck a cada navegação — exatamente o que o
                    island existe para impedir. O retro-moved borbulha até aqui.
                --}}
                <div x-on:retro-moved="$wire.selectByIndex($event.detail.index)">
                    @island(name: 'deck')
                        {{-- No desktop a tira ocupa o rodapé da viewport: o deck cede
                             essa altura para não ficar com o próprio rodapé coberto. No
                             mobile a tira rola junto com a página e o deck fica inteiro. --}}
                        <div class="retro-embed h-[75vh] w-full border border-gray-200 xl:h-[58vh] dark:border-white/10">
                            @include('portal::community-retrospective', $this->deck)
                        </div>
                    @endisland
                </div>
            </x-filament::section>
        </div>
        {{--
            Coluna 2: inspector. Contextual, quatro modos, escreve onde a Fase 2
            escrevia. Sem cabeçalho próprio: a Section de cada modo já nomeia o alvo
            ("Bloco: Discord") e carrega o ícone do modo — um título genérico em cima
            só repetia isso de forma mais vaga.
        --}}
        <div class="min-w-0">
            {{ $this->form }}
        </div>
    </div>

    {{--
        A tira: a estrutura do deck deitada no rodapé, em miniaturas do slide de
        verdade. Embaixo e não à esquerda porque um deck é uma SEQUÊNCIA — uma
        lista vertical em coluna estreita nunca mostrou isso — e porque a coluna
        cobrava do preview a largura que ele mais precisa.

        Sai do catálogo, não da composição: fonte desligada e slide oculto
        continuam aqui, apagados. É nesta tira que mora o botão que os religa; se
        sumissem junto com a composição, não haveria caminho de volta.
    --}}
    {{--
        Grudada no rodapé a partir do desktop. A tira é o mapa do deck: rolar até
        o fim da página para trocar de slide devolveria a fricção que tirá-la da
        lateral resolveu. No mobile ela volta a ser conteúdo normal — não sobra
        altura para gastar um pedaço fixo da tela.

        `sticky` depende de nenhum ancestral cortar o overflow; o wrapper de página
        do Filament não corta.
    --}}
    <x-filament::section class="xl:sticky xl:bottom-0 xl:z-20 xl:shadow-lg">
        <x-slot name="heading">Estrutura</x-slot>
        <x-slot name="description">
            Clique numa miniatura para inspecionar. O interruptor liga e desliga a fonte inteira; as setas mudam a ordem no deck.
        </x-slot>

        {{--
            Island, pelo mesmo motivo do deck: digitar no inspector re-renderiza a
            página, e sem o island cada tecla remontaria o deck inteiro em
            miniatura. A tira só re-renderiza quando algo foi salvo.

            Por isso o destaque do slide atual é do CLIENTE, não do servidor: o
            island não acompanha a seleção, mas os dois eventos que o deck já
            fala — `retro-goto` (o servidor mandou o deck pular) e `retro-moved`
            (o operador navegou por dentro) — dizem tudo o que a tira precisa.
        --}}
        {{--
            O listener fica FORA do island pelo mesmo motivo do deck: uma ação
            disparada de um elemento de dentro vira uma chamada escopada ao island —
            o inspector não atualizaria no mesmo roundtrip e cada clique pagaria o
            re-render da tira inteira. Os botões só despacham filmstrip-call; o
            evento borbulha até aqui e a chamada nasce fora do escopo do island.
        --}}
        <div x-on:filmstrip-call="$wire.$call($event.detail.method, ...$event.detail.args)">
        @island(name: 'filmstrip')
            {{-- Nome de classe completo, sem import: o corpo da island compila para
                 um arquivo próprio, onde nem o import do topo da view nem um
                 `@use` aqui sobrevivem.

                 E nada de escrever as diretivas de bloco do Blade dentro de um
                 comentário: o compilador casa a diretiva do COMENTÁRIO com o
                 fechamento de verdade e engole o bloco. --}}
            @php
                $deck = $this->deck;
                $groups = $this->filmstrip;
                $closingIndex = count($this->composedKinds) + 1;
            @endphp

            <div
                class="flex items-start gap-3 overflow-x-auto pb-2"
                x-data="{
                    active: @js($this->previewIndex()),
                    focus(index) {
                        if (index === null || index === undefined) return;

                        this.active = index;

                        this.$nextTick(() => {
                            this.$el
                                .querySelector(`[data-deck-index='${index}']`)
                                ?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                        });
                    },
                }"
                x-on:retro-goto.window="focus($event.detail.index)"
                x-on:retro-moved.window="focus($event.detail.index)"
            >
                {{-- Capa: o slide 0, sem fonte e sem on/off. --}}
                <x-panel-admin::retrospective.filmstrip-group :label="\He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\InspectorMode::Cover->getLabel()" :icon="\He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\InspectorMode::Cover->getIcon()">
                    <x-panel-admin::retrospective.filmstrip-thumb
                        :index="0"
                        label="Abertura"
                        :selection="\He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\InspectorMode::Cover->value"
                    >
                        <x-portal::retro.slides.cover
                            :sources="$deck['sources']"
                            :since="$deck['since']"
                            :until="$deck['until']"
                            :coverTitle="$deck['coverTitle']"
                            :coverIntro="$deck['coverIntro']"
                        />
                    </x-panel-admin::retrospective.filmstrip-thumb>
                </x-panel-admin::retrospective.filmstrip-group>

                @foreach ($groups as $index => $group)
                    <x-panel-admin::retrospective.filmstrip-group
                        :label="$group->label"
                        :group="$group"
                        :first="$index === 0"
                        :last="$index === count($groups) - 1"
                    >
                        @forelse ($group->slides as $slide)
                            <x-panel-admin::retrospective.filmstrip-thumb
                                :index="$slide->index"
                                :label="$slide->label"
                                :muted="! $slide->visible || ! $group->visible"
                                :selection="\He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\InspectorMode::Slide->value . ':' . $slide->kind"
                                :view="$slide->view"
                                :props="$slide->props"
                            />
                        @empty
                            {{-- Fonte registrada que não rendeu nada no recorte. O bloco
                                 fica, com o motivo à mostra: some da tira só o que nunca
                                 existiu. --}}
                            <div class="flex h-[117px] w-52 shrink-0 items-center justify-center rounded-lg border border-dashed border-gray-300 px-3 text-center text-xs text-gray-400 dark:border-white/10 dark:text-gray-500">
                                Sem dado neste recorte
                            </div>
                        @endforelse
                    </x-panel-admin::retrospective.filmstrip-group>
                @endforeach

                {{-- Fecho: sempre o último slide do deck. --}}
                <x-panel-admin::retrospective.filmstrip-group :label="\He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\InspectorMode::Closing->getLabel()" :icon="\He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\InspectorMode::Closing->getIcon()">
                    <x-panel-admin::retrospective.filmstrip-thumb
                        :index="$closingIndex"
                        label="Encerramento"
                        :selection="\He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support\InspectorMode::Closing->value"
                    >
                        <x-portal::retro.slides.closing
                            :sources="$deck['sources']"
                            :since="$deck['since']"
                            :until="$deck['until']"
                            :closingText="$deck['closingText']"
                        />
                    </x-panel-admin::retrospective.filmstrip-thumb>
                </x-panel-admin::retrospective.filmstrip-group>
            </div>
        @endisland
        </div>
    </x-filament::section>
</x-filament-panels::page>
