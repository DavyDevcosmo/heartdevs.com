<?php

declare(strict_types=1);

namespace He4rt\Portal\Retrospective;

use Illuminate\Support\Facades\View;

/**
 * Convenção que liga um slide à sua view no portal. Dona única do mapeamento
 * kind -> partial: o deck renderiza por aqui e o Deck Builder do painel mostra o
 * mesmo caminho ao operador, então não há como um lado desviar do outro.
 *
 * O kind vira caminho direto ("discord.new_members" =>
 * retro/slides/discord/new-members.blade.php); underscore vira hífen porque o
 * kind é identificador de dado e o arquivo segue o kebab-case das views.
 */
final class SlideView
{
    public static function kind(string $kind): string
    {
        return 'portal::retro.slides.'.str_replace('_', '-', $kind);
    }

    public static function cover(): string
    {
        return 'portal::components.retro.slides.cover';
    }

    public static function closing(): string
    {
        return 'portal::components.retro.slides.closing';
    }

    /**
     * Caminho do arquivo relativo à raiz do projeto, pronto para abrir no editor.
     *
     * Resolvido pelo finder do Blade, não montado com string: um kind sem partial
     * (snapshot congelado antes de a view existir, ou renomeada depois) devolve
     * null em vez de um caminho que mente.
     */
    public static function path(string $view): ?string
    {
        if (!View::exists($view)) {
            return null;
        }

        $absolute = View::getFinder()->find($view);

        return str_starts_with($absolute, base_path())
            ? mb_ltrim(mb_substr($absolute, mb_strlen(base_path())), '/')
            : $absolute;
    }
}
