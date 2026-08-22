<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_short_link_clicks', static function (Blueprint $table): void {
            // Divergência consciente do padrão UUID do projeto (ver §6 da spec):
            // tabela append-only de alto volume, onde UUID v4 fragmenta e infla o
            // índice B-tree da PK. A chave é sequencial de propósito.
            $table->bigIncrements('id');

            $table->foreignUuid('short_link_id')->constrained('marketing_short_links')->cascadeOnDelete();
            $table->timestampTz('clicked_at')->index();

            // ⚠︎ Dado pessoal (LGPD, §8 da spec): gravado cru e sem retenção, por decisão explícita.
            $table->string('ip_address', 45);
            $table->text('user_agent');

            $table->text('referer')->nullable();
            $table->char('country_code', 2)->nullable()->comment('Header CF-IPCountry do Cloudflare.');

            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();

            $table->boolean('is_bot')->default(value: false)->index();
            $table->string('bot_name')->nullable();

            // O que veio NA URL curta — evidência de origem, não o UTM configurado no link.
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();

            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();

            // O índice de `short_link_id` sozinho seria redundante: o composto abaixo
            // já o cobre como prefixo, e um índice a menos numa tabela append-only
            // de alto volume é escrita mais barata.
            $table->index(['short_link_id', 'clicked_at'], 'idx_short_link_clicks_timeline');
            $table->index('country_code', 'idx_short_link_clicks_country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_short_link_clicks');
    }
};
