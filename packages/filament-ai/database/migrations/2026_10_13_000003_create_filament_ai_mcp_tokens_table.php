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
        $column = (string) config('filament-ai.tenant_column', 'tenant_id');
        $type = (string) config('filament-ai.tenant_key_type', 'int');
        $userKeyType = (string) config('filament-ai.user_key_type', 'int');

        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $blueprint) use ($column, $type, $userKeyType): void {
            $blueprint->id();

            if ($type === 'uuid') {
                $blueprint->uuid($column)->nullable()->index();
            } else {
                $blueprint->unsignedBigInteger($column)->nullable()->index();
            }

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
