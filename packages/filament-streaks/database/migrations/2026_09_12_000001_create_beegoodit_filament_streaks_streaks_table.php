<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beegoodit_filament_streaks_streaks', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('strategy_identifier');
            $table->uuidMorphs('subject');
            $table->nullableUuidMorphs('scope');
            $table->date('start_at');
            $table->date('end_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['strategy_identifier', 'end_at']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            // COALESCE so null scope rows collide (PostgreSQL treats NULL as distinct in UNIQUE).
            DB::statement(
                'CREATE UNIQUE INDEX beegoodit_filament_streaks_streaks_open_unique '
                .'ON beegoodit_filament_streaks_streaks ('
                .'subject_type, subject_id, strategy_identifier, '
                .'COALESCE(scope_type, \'\'), COALESCE(scope_id::text, \'\')'
                .') WHERE end_at IS NULL'
            );
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS beegoodit_filament_streaks_streaks_open_unique');
        }

        Schema::dropIfExists('beegoodit_filament_streaks_streaks');
    }
};
