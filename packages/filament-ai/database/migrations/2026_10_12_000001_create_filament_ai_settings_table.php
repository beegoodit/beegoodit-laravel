<?php

declare(strict_types=1);

use BeegoodIT\FilamentAi\Database\LegacySubjectColumn;
use BeegoodIT\FilamentAi\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = Setting::TABLE;

        Schema::create($table, function (Blueprint $blueprint): void {
            $blueprint->id();

            LegacySubjectColumn::add($blueprint, unique: true);

            $blueprint->boolean('is_enabled')->default(false);
            $blueprint->boolean('is_mcp_enabled')->default(false);
            $blueprint->string('provider')->nullable();
            $blueprint->string('model')->nullable();
            $blueprint->text('api_key')->nullable();
            $blueprint->json('capabilities')->nullable();
            $blueprint->text('custom_instructions')->nullable();
            $blueprint->timestamps();
        });

        LegacySubjectColumn::ensureSingleNullSubject($table);
    }

    public function down(): void
    {
        Schema::dropIfExists(Setting::TABLE);
    }
};
