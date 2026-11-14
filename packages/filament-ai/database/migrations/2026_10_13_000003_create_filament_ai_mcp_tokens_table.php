<?php

declare(strict_types=1);

use BeegoodIT\FilamentAi\Database\LegacySubjectColumn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = (string) config('filament-ai.mcp.tokens_table', 'beegoodit_filament_ai_mcp_tokens');
        $userKeyType = (string) config('filament-ai.user_key_type', 'int');

        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $blueprint) use ($userKeyType): void {
            $blueprint->id();

            LegacySubjectColumn::add($blueprint);

            if ($userKeyType === 'uuid') {
                $blueprint->uuid('created_by_user_id')->nullable()->index();
            } else {
                $blueprint->unsignedBigInteger('created_by_user_id')->nullable()->index();
            }
            $blueprint->string('label');
            $blueprint->string('token_hash', 64)->unique();
            $blueprint->string('scope', 16)->default('read');
            $blueprint->timestamp('active_from')->useCurrent();
            $blueprint->timestamp('active_to');
            $blueprint->timestamp('revoked_at')->nullable()->index();
            $blueprint->timestamp('last_used_at')->nullable();
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists((string) config('filament-ai.mcp.tokens_table', 'beegoodit_filament_ai_mcp_tokens'));
    }
};
