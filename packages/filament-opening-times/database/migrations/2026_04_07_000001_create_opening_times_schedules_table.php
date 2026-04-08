<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opening_times_schedules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('openable_type');
            $table->uuid('openable_id');
            $table->string('timezone', 64);
            $table->timestamp('active_from');
            $table->timestamp('active_to');
            $table->timestamps();

            $table->index(['openable_type', 'openable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opening_times_schedules');
    }
};
