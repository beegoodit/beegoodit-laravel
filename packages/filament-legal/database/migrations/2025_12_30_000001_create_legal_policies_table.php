<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('legal_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type'); // privacy, terms, imprint, cookie
            $table->string('version'); // e.g., 1.0, 2.0
            $table->json('content'); // Translatable content
            $table->boolean('is_active')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['type', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_policies');
    }
};
