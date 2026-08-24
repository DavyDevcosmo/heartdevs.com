<?php

declare(strict_types=1);

namespace He4rt\Community\Retrospective\Actions;

use He4rt\Community\Retrospective\Contracts\PersonDirectory;
use He4rt\Community\Retrospective\DTOs\PersonAccount;
use He4rt\Community\Retrospective\DTOs\PersonIdentity;
use He4rt\Identity\User\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Traduz ids de usuário nas PersonIdentity que as fontes sabem medir.
 *
 * O vínculo entre plataformas já existe no identity — um User pendura suas
 * contas em `providers` — então o slide da tag não precisa da tabela de
 * tradução que o ADR-0001 diz não existir: ele pergunta ao dono das identidades.
 *
 * Carrega todo mundo de uma vez: são poucas pessoas por deck, mas resolver uma a
 * uma dentro do loop de composição custaria duas queries por cartão.
 */
final readonly class ResolvePeople implements PersonDirectory
{
    /**
     * @param  list<string>  $userIds
     * @return array<string, PersonIdentity> id do usuário => pessoa
     */
    public function execute(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        /** @var Collection<int, User> $users */
        $users = User::query()
            // `whereNull('disconnected_at')` e não o scope `activelyConnected`: ele
            // exige access_token guardado, e só 210 das ~50 mil contas o têm (o
            // token expira e não é renovado). Para MEDIR o passado basta a conta
            // estar vinculada — token válido é requisito de quem vai CHAMAR a API.
            ->with(['providers' => static fn (Relation $query): Relation => $query->whereNull('disconnected_at')])
            ->whereIn('id', array_values(array_unique($userIds)))
            ->get();

        $people = [];

        foreach ($users as $user) {
            $accounts = $this->accounts($user);

            $people[$user->id] = new PersonIdentity(
                userId: $user->id,
                name: $user->name,
                username: $user->username,
                avatar: $this->avatar($user, $accounts),
                accounts: $accounts,
            );
        }

        return $people;
    }

    /**
     * Uma conta por provider. Empate (a mesma plataforma conectada duas vezes)
     * fica com a mais recente: é a que o resto do sistema considera ativa.
     *
     * @return array<string, PersonAccount>
     */
    private function accounts(User $user): array
    {
        $accounts = [];

        foreach ($user->providers->sortBy('connected_at') as $identity) {
            $metadata = is_array($identity->metadata) ? $identity->metadata : [];

            $accounts[$identity->provider->value] = new PersonAccount(
                identityId: $identity->id,
                accountId: $identity->external_account_id,
                username: $this->stringOrNull($metadata['username'] ?? null),
                avatar: $this->stringOrNull($metadata['avatar'] ?? null),
            );
        }

        return $accounts;
    }

    /**
     * Cascata do mais próprio para o mais genérico: a foto que a pessoa subiu
     * aqui, depois a das plataformas, e só então o fallback pelo username.
     *
     * O fallback é o último a entrar de propósito — `getFilamentAvatarUrl()` monta
     * a URL do GitHub com o username DO SITE, que na maioria das contas não existe
     * lá e devolve a imagem de erro.
     *
     * @param  array<string, PersonAccount>  $accounts
     */
    private function avatar(User $user, array $accounts): string
    {
        $uploaded = $user->getFirstMediaUrl('avatar');

        if ($uploaded !== '') {
            return $uploaded;
        }

        foreach ($accounts as $account) {
            if ($account->avatar !== null && $account->avatar !== '') {
                return $account->avatar;
            }
        }

        return sprintf('https://github.com/%s.png', $user->username);
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
