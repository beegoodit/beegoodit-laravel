<?php

declare(strict_types=1);

use BeegoodIT\FilamentAi\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = Setting::TABLE;
        $column = (string) config('filament-ai.tenant_column', 'tenant_id');
        $type = (string) config('filament-ai.tenant_key_type', 'int');

        Schema::create($table, function (Blueprint $blueprint) use ($column, $type): void {
            $blueprint->id();

            if ($type === 'uuid') {
                $blueprint->uuid($column)->nullable()->unique();
            } else {
                $blueprint->unsignedBigInteger($column)->nullable()->unique();
            }

            $blueprint->boolean('enabled')->default(false);
            $blueprint->boolean('mcp_enabled')->default(false);
            $blueprint->string('provider')->nullable();
            $blueprint->string('model')->nullable();
            $blueprint->text('api_key')->nullable();
            $blueprint->json('capabilities')->nullable();
            $blueprint->text('custom_instructions')->nullable();
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Setting::TABLE);
    }
};
