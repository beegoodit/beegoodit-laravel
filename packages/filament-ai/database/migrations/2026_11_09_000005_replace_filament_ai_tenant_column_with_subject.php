<?php

declare(strict_types=1);

use BeegoodIT\FilamentAi\Database\LegacySubjectColumn;
use BeegoodIT\FilamentAi\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        LegacySubjectColumn::convert(Setting::TABLE, singleNullSubject: true, uniquePair: true);

        LegacySubjectColumn::convert(
            (string) config('filament-ai.mcp.tokens_table', 'beegoodit_filament_ai_mcp_tokens'),
        );

        LegacySubjectColumn::convert(
            (string) config('ai.conversations.tables.conversations', 'agent_conversations'),
        );
    }

    public function down(): void
    {
        // Legacy tenant columns are not restored. Restore from backup if required.
    }
};
