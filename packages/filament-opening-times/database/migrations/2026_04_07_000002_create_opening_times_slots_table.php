<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opening_times_slots', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('schedule_id')->constrained('opening_times_schedules')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('opens_at');
            $table->time('closes_at');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['schedule_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opening_times_slots');
    }
};
