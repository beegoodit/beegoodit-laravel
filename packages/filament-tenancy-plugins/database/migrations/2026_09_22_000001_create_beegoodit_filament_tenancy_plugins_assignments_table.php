<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beegoodit_filament_tenancy_plugins_assignments', function (Blueprint $table): void {
            $table->id();
            $table->string('pluginable_type');
            $table->string('pluginable_id');
            $table->string('plugin');
            $table->timestamps();

            $table->unique(
                ['pluginable_type', 'pluginable_id', 'plugin'],
                'ftp_assignments_pluginable_plugin_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beegoodit_filament_tenancy_plugins_assignments');
    }
};
