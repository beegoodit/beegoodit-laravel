<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('filament-connect.table_name', 'api_accounts'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('service');
            $table->string('name');
            $table->text('credentials'); // Encrypted
            $table->uuidMorphs('owner');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['service', 'owner_id', 'owner_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('filament-connect.table_name', 'api_accounts'));
    }
};
