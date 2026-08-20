<?php

declare(strict_types=1);

namespace He4rt\Contents\Articles\Console;

use He4rt\Contents\Articles\Actions\UpsertArticle;
use He4rt\Contents\Articles\ArticleProviderRegistry;
use He4rt\Contents\Articles\Contracts\ArticleProvider;
use He4rt\Contents\Articles\Contracts\DiscoversByIdentity;
use He4rt\Contents\Articles\Contracts\DiscoversBySource;
use He4rt\Contents\Articles\Contracts\HydratesDetail;
use He4rt\Contents\Articles\DTOs\ArticleDTO;
use He4rt\Contents\Articles\Models\Article;
use He4rt\Contents\Models\ContentEntry;
use He4rt\Identity\ExternalIdentity\Enums\IdentityProvider;
use He4rt\Identity\ExternalIdentity\Models\ExternalIdentity;
use He4rt\Identity\User\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

#[Description(description: 'Sync articles from all registered content providers into the canonical catalogue')]
#[Signature(signature: 'contents:sync-articles')]
final class SyncArticlesCommand extends Command
{
    public function handle(ArticleProviderRegistry $registry, UpsertArticle $upsert): int
    {
        foreach ($registry->all() as $provider) {
            try {
                if ($provider instanceof DiscoversBySource) {
                    foreach ($provider->fetchFromSource() as $dto) {
                        $upsert->execute($provider->provider(), $this->hydrateIfStale($provider, $dto));
                    }
                }

                if ($provider instanceof DiscoversByIdentity) {
                    foreach ($this->connectedIdentitiesFor($provider) as $identity) {
                        foreach ($provider->fetchForIdentity($identity) as $dto) {
                            $upsert->execute($provider->provider(), $this->hydrateIfStale($provider, $dto));
                        }
                    }
                }
            } catch (Throwable $exception) {
                Log::error('contents: provider sync failed', [
                    'provider' => $provider->provider()->value,
                    'exception' => $exception,
                ]);
            }
        }

        return self::SUCCESS;
    }

    private function hydrateIfStale(ArticleProvider $provider, ArticleDTO $dto): ArticleDTO
    {
        $entry = ContentEntry::query()
            ->with('contentable')
            ->where('provider', $provider->provider())
            ->where('external_id', $dto->externalId)
            ->first();

        $stored = $entry?->contentable instanceof Article ? $entry->contentable->source_edited_at : null;

        $isStale = $entry === null
            || $stored?->getTimestamp() !== $dto->sourceEditedAt?->getTimestamp();

        return $isStale && $provider instanceof HydratesDetail
            ? $provider->fetchDetail($dto)
            : $dto;
    }

    /** @return iterable<ExternalIdentity> */
    private function connectedIdentitiesFor(ArticleProvider $provider): iterable
    {
        $identityProvider = $provider->provider()->toIdentityProvider();

        if (!$identityProvider instanceof IdentityProvider) {
            return [];
        }

        return ExternalIdentity::query()
            ->whereMorphedTo('model', User::class)
            ->where('provider', $identityProvider)
            ->whereNotNull('connected_at')
            ->whereNull('disconnected_at')
            ->get();
    }
}
