<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beegoodit_filament_streaks_operation_runs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->json('params');
            $table->string('status');
            $table->json('result')->nullable();
            $table->text('error')->nullable();
            $table->foreignUuid('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index('requested_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beegoodit_filament_streaks_operation_runs');
    }
};
