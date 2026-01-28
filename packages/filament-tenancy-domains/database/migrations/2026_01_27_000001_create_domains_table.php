<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('domain')->unique();
            $table->string('type'); // platform, custom_subdomain, custom_domain
            $table->uuidMorphs('model');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('verification_token')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_verification_attempt_at')->nullable();
            $table->text('last_verification_error')->nullable();
            $table->timestamps();

            // Only one primary domain per model
            $table->unique(['model_id', 'model_type', 'is_primary'], 'unique_primary_domain_per_model')
                ->where('is_primary', true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
