<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_item_attachments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('feed_item_id')->constrained('feed_items')->cascadeOnDelete();
            $table->string('type', 50)->default('file');
            $table->string('path');
            $table->string('filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['feed_item_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_item_attachments');
    }
};
