<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('legal_identities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('owner_id');
            $table->string('owner_type');
            $table->string('name');
            $table->string('form')->nullable();
            $table->string('representative')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('vat_id')->nullable();
            $table->string('register_court')->nullable();
            $table->string('register_number')->nullable();
            $table->timestamps();

            $table->unique(['owner_id', 'owner_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_identities');
    }
};
