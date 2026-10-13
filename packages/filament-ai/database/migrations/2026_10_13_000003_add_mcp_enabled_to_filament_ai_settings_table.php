<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = (string) config('filament-ai.settings_table', 'beegoodit_filament_ai_settings');

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'mcp_enabled')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->boolean('mcp_enabled')->default(false)->after('enabled');
        });
    }

    public function down(): void
    {
        $table = (string) config('filament-ai.settings_table', 'beegoodit_filament_ai_settings');

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'mcp_enabled')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->dropColumn('mcp_enabled');
        });
    }
};
