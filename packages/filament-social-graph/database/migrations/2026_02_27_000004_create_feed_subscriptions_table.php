<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_subscriptions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('subscriber_type', 255);
            $table->uuid('subscriber_id');
            $table->string('feed_owner_type', 255);
            $table->uuid('feed_owner_id');
            $table->foreignUuid('subscription_rule_id')
                ->nullable()
                ->after('feed_owner_id')
                ->constrained('feed_subscription_rules')
                ->nullOnDelete();
            $table->timestamps();

            $table->unique(['subscriber_type', 'subscriber_id', 'feed_owner_type', 'feed_owner_id']);
            $table->index(['feed_owner_type', 'feed_owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_subscriptions');
    }
};
