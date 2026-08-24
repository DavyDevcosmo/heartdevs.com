{{--
    O slide da entrega da tag. `data-steps` é o contrato que o deck lê: enquanto
    houver passo pendente, a seta direita revela em vez de trocar de slide.

    A conta é 1 + 3 por pessoa — o passo 0 abre com a pergunta e a tela vazia, e
    cada pessoa gasta três: quem é, o que fez, e por que a tag é dela. Quem
    apresenta controla o tempo de cada uma dessas frases.
--}}
<section class="slide promo-tag" data-label="A tag He4rt" data-steps="{{ 1 + count($cards) * 3 }}">
    <div class="slide-inner" style="text-align: center">
        <span class="sec-tag" data-reveal="0" style="margin-inline: auto">O ritual</span>
        <h2 class="sec" data-reveal="0">A tag He4rt vai para…</h2>

        <div class="promo-stage">
            @foreach ($cards as $position => $card)
                @php ($step = $position * 3 + 1)
                <article class="promo-hero" data-reveal="{{ $step }}">
                    <img
                        class="promo-avatar big"
                        src="{{ $card->avatar }}"
                        width="112"
                        height="112"
                        alt="{{ $card->name }}"
                    />

                    <div class="promo-name big">{{ $card->name }}</div>
                    <div class="promo-handle">{{ '@' . $card->username }}</div>

                    <div class="promo-hero-metrics" data-reveal="{{ $step + 1 }}">
                        @foreach ($card->groups as $group)
                            <div class="promo-source">
                                <span class="promo-source-name">{{ $group->sourceLabel }}</span>
                                <span class="promo-metrics">
                                    @foreach ($group->metrics as $metric)
                                        <span class="promo-metric">
                                            <b>{{ number_format($metric->value, 0, ',', '.') }}</b>
                                            {{ $metric->label }}
                                        </span>
                                    @endforeach
                                </span>
                            </div>
                        @endforeach
                    </div>

                    @if (filled($card->reason))
                        <p class="promo-reason big" data-reveal="{{ $step + 2 }}">{{ $card->reason }}</p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
