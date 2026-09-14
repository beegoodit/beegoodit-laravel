<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_entities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('owner_type')->nullable();
            $table->string('owner_id')->nullable();
            $table->string('name');
            $table->string('address_line');
            $table->string('address_line_2')->nullable();
            $table->string('postal_code', 32);
            $table->string('city');
            $table->string('region')->nullable();
            $table->string('email');
            $table->string('phone', 64)->nullable();
            $table->char('country', 2);
            $table->json('profile')->nullable();
            $table->timestamps();

            $table->unique(['owner_type', 'owner_id']);
            $table->index('country');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_entities');
    }
};
