<?php

declare(strict_types=1);

namespace He4rt\Marketing\ShortLink\Actions;

use He4rt\Marketing\ShortLink\DTOs\ShortLinkChanges;
use He4rt\Marketing\ShortLink\Exceptions\InvalidDestinationUrl;
use He4rt\Marketing\ShortLink\Models\ShortLink;
use He4rt\Marketing\ShortLink\Support\DestinationUrlValidator;
use He4rt\Marketing\ShortLink\Support\ShortLinkCache;
use Illuminate\Support\Facades\DB;

/**
 * Edits a short link and, when the destination moved, records the move as a fact.
 *
 * A blind `update(['destination_url' => …])` would make the click chart lie: you
 * would see 1.284 clicks on `/l/discord-a3f9k` with no way to know half of them
 * went to the old invite. Here the previous interval is closed at `now()` and a
 * new one opens at the same instant — no gap, no overlap, never two open rows.
 */
final readonly class UpdateShortLink
{
    /**
     * @throws InvalidDestinationUrl
     */
    public function execute(ShortLink $link, ShortLinkChanges $changes): ShortLink
    {
        $newDestination = $changes->destinationUrl();

        if ($newDestination !== null) {
            DestinationUrlValidator::assert($newDestination);
        }

        $link = DB::transaction(function () use ($link, $changes): ShortLink {
            // Asked while the model still holds the old values — after `fill()`
            // there is nothing left to compare against.
            $destinationChanged = $changes->hasDestinationChange($link);

            $link->fill($changes->toAttributes())->save();

            if ($destinationChanged) {
                $changedAt = now();

                $link->destinations()
                    ->whereNull('valid_until')
                    ->update(['valid_until' => $changedAt]);

                $link->destinations()->create([
                    'destination_url' => $link->destination_url,
                    'utm' => $link->utm,
                    'changed_by' => $changes->changedBy ?? auth()->id(),
                    'valid_from' => $changedAt,
                    'valid_until' => null,
                ]);
            }

            return $link;
        });

        // Belt and braces alongside the observer, and deliberately after commit:
        // "I changed the destination and it works right now" is the whole point.
        ShortLinkCache::forget($link->slug);

        return $link;
    }
}
