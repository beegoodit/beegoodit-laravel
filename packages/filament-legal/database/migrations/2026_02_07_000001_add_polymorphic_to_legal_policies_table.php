<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('legal_policies', function (Blueprint $table) {
            $table->uuid('owner_id')->nullable()->after('id');
            $table->string('owner_type')->nullable()->after('owner_id');

            $table->dropUnique(['type', 'version']);
            $table->unique(['type', 'version', 'owner_id', 'owner_type']);
        });
    }

    public function down(): void
    {
        Schema::table('legal_policies', function (Blueprint $table) {
            $table->dropUnique(['type', 'version', 'owner_id', 'owner_type']);
            $table->unique(['type', 'version']);

            $table->dropColumn(['owner_id', 'owner_type']);
        });
    }
};
