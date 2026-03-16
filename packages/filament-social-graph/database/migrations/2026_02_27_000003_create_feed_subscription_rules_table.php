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
            $table->string('subscribable_type', 255);
            $table->uuid('subscribable_id');
            $table->string('scope', 64);
            $table->boolean('auto_subscribe')->default(false);
            $table->boolean('unsubscribable')->default(false);
            $table->timestamps();

            $table->unique(['subscribable_type', 'subscribable_id']);
            $table->index(['subscribable_type', 'subscribable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_subscription_rules');
    }
};
