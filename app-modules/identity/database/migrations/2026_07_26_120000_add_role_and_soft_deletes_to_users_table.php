<?php

declare(strict_types=1);

use He4rt\Identity\User\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', static function (Blueprint $table): void {
            $table->string('role')->default(Role::Member->value)->after('is_donator');
            $table->softDeletesTz();
        });

        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_username_unique');

        // Scoped to active rows only: soft-deleted users must not block a
        // username from being reused (e.g. account merges reassign it).
        DB::statement('CREATE UNIQUE INDEX users_username_unique ON users (username) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_username_unique');
        DB::statement('DROP INDEX IF EXISTS users_username_unique');

        // up() only enforced uniqueness among active rows, so a soft-deleted
        // row may share a username with an active row (or another trashed
        // row). Rename those duplicates before restoring the global unique
        // constraint below, keeping the active row (or the oldest one, if
        // all are trashed) untouched. The suffix is intentionally neutral
        // (not "_deleted_"): the renamed row isn't necessarily trashed.
        DB::statement(<<<'SQL'
            UPDATE users
            SET username = username || '_dup_' || substr(id::text, 1, 8)
            WHERE id IN (
                SELECT id
                FROM (
                    SELECT id, row_number() OVER (
                        PARTITION BY username
                        ORDER BY deleted_at IS NULL DESC, created_at ASC
                    ) AS row_number
                    FROM users
                ) ranked
                WHERE row_number > 1
            )
        SQL);

        Schema::table('users', static function (Blueprint $table): void {
            $table->unique('username');
            $table->dropColumn(['role', 'deleted_at']);
        });
    }
};
