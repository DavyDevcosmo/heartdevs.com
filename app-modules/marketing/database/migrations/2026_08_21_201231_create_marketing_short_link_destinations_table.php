<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_short_link_destinations', static function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('short_link_id')->constrained('marketing_short_links')->cascadeOnDelete();

            $table->text('destination_url');
            $table->jsonb('utm')->nullable()->comment('VO UtmParameters vigente neste intervalo.');

            $table->foreignUuid('changed_by')->nullable()->constrained('users')->nullOnDelete();

            // Histórico append-only de intervalos [valid_from, valid_until).
            // valid_until = null marca a linha vigente — há no máximo uma por link.
            $table->timestampTz('valid_from');
            $table->timestampTz('valid_until')->nullable();

            $table->timestampTz('created_at')->nullable();

            $table->index(['short_link_id', 'valid_from'], 'idx_short_link_destinations_timeline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_short_link_destinations');
    }
};
