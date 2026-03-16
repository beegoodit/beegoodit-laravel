<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_subscription_rules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('feed_id')->constrained('feeds')->cascadeOnDelete();
            $table->string('scope', 64);
            $table->boolean('auto_subscribe')->default(false);
            $table->boolean('unsubscribable')->default(false);
            $table->timestamps();

            $table->index('feed_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_subscription_rules');
    }
};
