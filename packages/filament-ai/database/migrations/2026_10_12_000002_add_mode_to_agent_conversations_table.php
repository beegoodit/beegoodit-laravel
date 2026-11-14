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

        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'mode')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->string('mode', 16)->default('ask')->after('title');
        });
    }

    public function down(): void
    {
        $table = (string) config('ai.conversations.tables.conversations', 'agent_conversations');

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'mode')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->dropColumn('mode');
        });
    }
};
