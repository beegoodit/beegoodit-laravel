<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entry_links', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('token')->unique();
            $table->string('slug')->nullable();
            $table->text('target_url');
            $table->unsignedSmallInteger('redirect_code')->default(302);
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('active_from')->default('1970-01-01 00:00:00');
            $table->timestamp('active_to')->default('9999-12-31 23:59:59');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_links');
    }
};
