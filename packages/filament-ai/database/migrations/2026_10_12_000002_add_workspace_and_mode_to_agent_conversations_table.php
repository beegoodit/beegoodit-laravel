<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = (string) config('ai.conversations.tables.conversations', 'agent_conversations');
        $column = (string) config('filament-ai.workspace_column', 'workspace_id');
        $type = (string) config('filament-ai.workspace_key_type', 'int');

        if (! Schema::hasTable($table)) {
            return;
        }

        if (! Schema::hasColumn($table, $column)) {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $type): void {
                if ($type === 'uuid') {
                    $blueprint->uuid($column)->nullable()->index();
                } else {
                    $blueprint->unsignedBigInteger($column)->nullable()->index();
                }
            });
        }

        if (! Schema::hasColumn($table, 'mode')) {
            Schema::table($table, function (Blueprint $blueprint) use ($column): void {
                $blueprint->string('mode', 16)->default('ask')->after($column);
            });
        }
    }

    public function down(): void
    {
        $table = (string) config('ai.conversations.tables.conversations', 'agent_conversations');
        $column = (string) config('filament-ai.workspace_column', 'workspace_id');

        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table, $column): void {
            if (Schema::hasColumn($table, 'mode')) {
                $blueprint->dropColumn('mode');
            }

            if (Schema::hasColumn($table, $column)) {
                $blueprint->dropColumn($column);
            }
        });
    }
};
