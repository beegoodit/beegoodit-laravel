<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('actor_type', 255);
            $table->uuid('actor_id');
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->json('attachments')->nullable();
            $table->string('visibility', 50)->default('public');
            $table->foreignUuid('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['actor_type', 'actor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_items');
    }
};
