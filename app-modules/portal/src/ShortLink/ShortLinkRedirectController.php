<?php

declare(strict_types=1);

namespace He4rt\Portal\ShortLink;

use He4rt\Marketing\ShortLink\Actions\ResolveShortLink;
use He4rt\Marketing\ShortLink\DTOs\ClickContext;
use He4rt\Marketing\ShortLink\Jobs\RecordShortLinkClick;
use He4rt\Marketing\ShortLink\ValueObjects\UtmParameters;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * A borda HTTP do encurtador: traduz a decisão do domínio em resposta.
 *
 * Quem decide se o slug pode redirecionar é o `marketing` — este controller não
 * consulta coluna, não checa `active` nem `expires_at` e não conhece cache. Ele
 * só pergunta e obedece, o que mantém o módulo de domínio livre de HTTP e este
 * módulo livre de regra de negócio.
 */
final readonly class ShortLinkRedirectController
{
    public function __construct(private ResolveShortLink $resolve) {}

    public function __invoke(Request $request, string $slug): RedirectResponse|Response
    {
        $resolution = $this->resolve->execute($slug);

        $id = $resolution->id;
        $destination = $resolution->destinationUrl;
        $utm = $resolution->utm;

        /*
         * `isRedirectable()` é a decisão; as três comparações com null ao lado
         * existem para o analisador estático, já que uma Resolution
         * redirecionável sempre carrega id, destino e UTM.
         */
        $cannotRedirect = !$resolution->isRedirectable()
            || $id === null
            || $destination === null
            || !$utm instanceof UtmParameters;

        if ($cannotRedirect) {
            /*
             * Um só desfecho para os quatro casos mortos — inexistente,
             * desativado, vencido e soft-deletado. Responder diferente por caso
             * transformaria a rota num oráculo de enumeração de slug.
             */
            return response()->view('portal::short-link-unavailable', status: 404);
        }

        /*
         * Só o caminho feliz gera clique: um slug morto não é tráfego de
         * campanha e não pode inflar a métrica de nenhum link.
         *
         * Vai o ClickContext achatado, nunca o Request — sessão, bindings e
         * uploads não sobrevivem à serialização da fila.
         */
        dispatch(new RecordShortLinkClick(ClickContext::fromRequest($request, $id)));

        /*
         * 302, nunca 301: um permanente ficaria no cache do browser e mataria
         * as duas razões do encurtador existir — o clique pararia de chegar
         * (sem métrica) e a troca de destino não valeria para quem já clicou.
         *
         * A query do clique entra no appendTo porque quem clicou com
         * `?utm_source=twitter` trouxe intenção mais específica que o UTM
         * configurado no link. Lida do InputBag e não de `$request->query()`
         * porque só o bag garante o `array<string, mixed>` que o VO espera.
         */
        return redirect()->away(
            $utm->appendTo($destination, $request->query->all()),
            status: 302,
        );
    }
}
