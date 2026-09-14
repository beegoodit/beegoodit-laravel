<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beegoodit_filament_streaks_notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('streak_id')->constrained('beegoodit_filament_streaks_streaks')->cascadeOnDelete();
            $table->string('period_key');
            $table->string('kind');
            $table->timestamp('sent_at');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['streak_id', 'period_key', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beegoodit_filament_streaks_notifications');
    }
};
