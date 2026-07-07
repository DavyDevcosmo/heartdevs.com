<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geo_cities', static function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('state_id')->nullable()->index();
            $table->string('country_code', 2)->index();
            $table->string('name');
            $table->timestampTz('synced_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_cities');
    }
};
