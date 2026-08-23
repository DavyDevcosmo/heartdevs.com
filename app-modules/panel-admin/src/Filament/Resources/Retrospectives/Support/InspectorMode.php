<?php

declare(strict_types=1);

namespace He4rt\PanelAdmin\Filament\Resources\Retrospectives\Support;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * Os quatro modos do inspector do Deck Builder. Não é escala ordenada — são
 * quatro alvos distintos de edição, cada um escrevendo onde a Fase 2 já
 * escrevia (ADR-0002) —, então cada caso tem cor própria, sem rampa.
 */
enum InspectorMode: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case Cover = 'cover';
    case Source = 'source';
    case Slide = 'slide';
    case Closing = 'closing';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cover => 'Capa',
            self::Source => 'Bloco de fonte',
            self::Slide => 'Slide',
            self::Closing => 'Fecho',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Cover => 'primary',
            self::Source => 'info',
            self::Slide => 'success',
            self::Closing => 'gray',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Cover => 'Título, recorte e texto de abertura — colunas da edição.',
            self::Source => 'Exibir a fonte e curar o que ela esconde do deck.',
            self::Slide => 'Exibir este tipo de slide. O toggle vale para o kind inteiro.',
            self::Closing => 'A mensagem que fecha o deck.',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Cover => Heroicon::OutlinedSparkles,
            self::Source => Heroicon::OutlinedSquares2x2,
            self::Slide => Heroicon::OutlinedRectangleGroup,
            self::Closing => Heroicon::OutlinedChatBubbleBottomCenterText,
        };
    }
}
