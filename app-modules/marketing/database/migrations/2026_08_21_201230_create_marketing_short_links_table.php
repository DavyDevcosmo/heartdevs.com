<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_short_links', static function (Blueprint $table): void {
            $table->uuid('id')->primary();

            // O slug é a identidade pública e permanente do link: único para sempre,
            // inclusive contra linhas soft-deletadas — nunca é reusado.
            $table->string('slug')->unique();
            $table->string('base_slug')->index()->comment('Apelido escrito pelo staff, sem o sufixo de unicidade.');

            $table->text('destination_url');
            $table->jsonb('utm')->nullable()->comment('VO UtmParameters — anexado ao destino no redirect.');
            $table->jsonb('tags')->default('[]')->comment('VO TagList — rótulos livres para agrupar links.');

            $table->boolean('active')->default(value: true);
            $table->timestampTz('expires_at')->nullable();

            $table->unsignedInteger('clicks_count')->default(0);
            $table->unsignedInteger('human_clicks_count')->default(0);

            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_short_links');
    }
};
