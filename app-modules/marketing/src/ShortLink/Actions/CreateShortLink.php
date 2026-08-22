<?php

declare(strict_types=1);

namespace He4rt\Marketing\ShortLink\Actions;

use He4rt\Marketing\ShortLink\DTOs\NewShortLinkData;
use He4rt\Marketing\ShortLink\Exceptions\InvalidDestinationUrl;
use He4rt\Marketing\ShortLink\Models\ShortLink;
use He4rt\Marketing\ShortLink\Support\DestinationUrlValidator;
use He4rt\Marketing\ShortLink\Support\ShortLinkCache;
use He4rt\Marketing\ShortLink\Support\SlugGenerator;
use Illuminate\Support\Facades\DB;

/**
 * Mints a short link and opens its first destination interval.
 *
 * The history row is not an afterthought written by the panel later: a link that
 * exists without an open `[valid_from, null)` interval would make every future
 * click un-attributable to a destination, so both rows are born in one transaction.
 */
final readonly class CreateShortLink
{
    /**
     * @throws InvalidDestinationUrl
     */
    public function execute(NewShortLinkData $data): ShortLink
    {
        // Guarded here, not in the Filament form: this Action is reachable from
        // a command, a test or a future bot, and `javascript:` must die in all of them.
        DestinationUrlValidator::assert($data->destinationUrl);

        $link = DB::transaction(function () use ($data): ShortLink {
            /** @var ShortLink $link */
            $link = ShortLink::query()->create([
                ...$data->toAttributes(),
                'slug' => SlugGenerator::for($data->nickname),
                'base_slug' => SlugGenerator::base($data->nickname),
            ]);

            $link->destinations()->create([
                'destination_url' => $link->destination_url,
                'utm' => $link->utm,
                'changed_by' => $data->createdBy,
                'valid_from' => now(),
                'valid_until' => null,
            ]);

            return $link;
        });

        // After commit, so a concurrent redirect cannot repopulate the key with
        // a row that is still invisible to it.
        ShortLinkCache::forget($link->slug);

        return $link;
    }
}
