@php ($noData = count($sources) === 0)
@use(He4rt\Portal\Retrospective\SlideView)
<x-portal::retro.deck :stateKey="$stateKey" :bare="$noData">
    @if ($noData)
        <x-portal::retro.slides.empty />
    @else
        <x-portal::retro.slides.cover
            :sources="$sources"
            :since="$since"
            :until="$until"
            :coverTitle="$coverTitle ?? null"
            :coverIntro="$coverIntro ?? null"
        />

        @foreach ($sources as $source)
            @foreach ($source->slides as $slide)
                @include(SlideView::kind($slide->kind()), $slide->toArray())
            @endforeach
        @endforeach

        <x-portal::retro.slides.closing
            :sources="$sources"
            :since="$since"
            :until="$until"
            :closingText="$closingText ?? null"
        />
    @endif
</x-portal::retro.deck>
