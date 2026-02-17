<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legal_identities', function (Blueprint $table) {
            $table->date('founded_at')->nullable()->after('register_number');
        });
    }

    public function down(): void
    {
        Schema::table('legal_identities', function (Blueprint $table) {
            $table->dropColumn('founded_at');
        });
    }
};
