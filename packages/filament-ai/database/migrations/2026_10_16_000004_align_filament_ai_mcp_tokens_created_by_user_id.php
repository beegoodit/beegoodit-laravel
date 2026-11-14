<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = (string) config('filament-ai.mcp.tokens_table', 'beegoodit_filament_ai_mcp_tokens');
        $userKeyType = (string) config('filament-ai.user_key_type', 'int');

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'created_by_user_id')) {
            return;
        }

        // Fresh installs already create the correct column type; SQLite cannot
        // reliably drop+readd indexed columns in place during tests.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->dropColumn('created_by_user_id');
        });

        Schema::table($table, function (Blueprint $blueprint) use ($userKeyType): void {
            if ($userKeyType === 'uuid') {
                $blueprint->uuid('created_by_user_id')->nullable()->index();
            } else {
                $blueprint->unsignedBigInteger('created_by_user_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        // No-op: type realignment is one-way for hosts.
    }
};
