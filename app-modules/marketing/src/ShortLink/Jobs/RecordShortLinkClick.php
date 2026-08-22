<?php

declare(strict_types=1);

namespace He4rt\Marketing\ShortLink\Jobs;

use DeviceDetector\DeviceDetector;
use He4rt\Marketing\ShortLink\DTOs\ClickContext;
use He4rt\Marketing\ShortLink\Models\ShortLink;
use He4rt\Marketing\ShortLink\Models\ShortLinkClick;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Everything expensive about a click, moved off the redirect.
 *
 * The visitor is already on Discord by the time this runs: User-Agent parsing,
 * the INSERT and the counter bumps all happen here so the 302 answers in
 * milliseconds and a 500-click spike only lengthens the queue, never the response.
 *
 * Preview crawlers (Discord, WhatsApp, Twitter, Slack) can produce 5–10 hits
 * before a human clicks. Their rows are kept — the raw data is the product — but
 * flagged `is_bot`, and only humans move `human_clicks_count`, so the number
 * staff reads in the panel is not inflated by unfurls.
 */
#[Backoff([10, 30, 120])]
#[Tries(tries: 3)]
final class RecordShortLinkClick implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(private readonly ClickContext $context) {}

    public function handle(): void
    {
        $detector = new DeviceDetector($this->context->userAgent ?? '');
        $detector->parse();

        $isBot = $detector->isBot();

        ShortLinkClick::query()->create([
            'short_link_id' => $this->context->shortLinkId,
            'clicked_at' => $this->context->clickedAt,
            // IP and User-Agent are stored raw and without retention — an explicit,
            // documented project decision. See §8.1 of the spec (LGPD).
            // Both columns are NOT NULL: a request without an IP or a User-Agent
            // still produced a click, so it is stored as empty rather than dropped.
            'ip_address' => $this->context->ip ?? '',
            'user_agent' => $this->context->userAgent ?? '',
            'referer' => $this->context->referer,
            'country_code' => $this->context->countryCode,
            'is_bot' => $isBot,
            'bot_name' => $isBot ? $this->botName($detector) : null,
            'device_type' => $isBot ? null : $detector->getDeviceName(),
            'browser' => $isBot ? null : $this->stringOrNull($detector->getClient('name')),
            'os' => $isBot ? null : $this->stringOrNull($detector->getOs('name')),
            'utm_source' => $this->context->utmSource,
            'utm_medium' => $this->context->utmMedium,
            'utm_campaign' => $this->context->utmCampaign,
            'user_id' => $this->context->userId,
        ]);

        // One UPDATE for both counters instead of two round-trips per click.
        $counters = ['clicks_count' => 1];

        if (!$isBot) {
            $counters['human_clicks_count'] = 1;
        }

        ShortLink::query()
            ->whereKey($this->context->shortLinkId)
            ->incrementEach($counters);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Failed to record short link click.', [
            'short_link_id' => $this->context->shortLinkId,
            'clicked_at' => $this->context->clickedAt->toIso8601String(),
            'exception' => $exception?->getMessage(),
        ]);
    }

    private function botName(DeviceDetector $detector): ?string
    {
        $bot = $detector->getBot();

        if (!is_array($bot)) {
            return null;
        }

        return $this->stringOrNull($bot['name'] ?? null);
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
