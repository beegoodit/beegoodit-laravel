<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('partnerable_type', 255)->nullable();
            $table->uuid('partnerable_id')->nullable();
            $table->string('type', 255)->default('partner');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('url', 1024)->nullable();
            $table->string('logo', 512)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamp('active_from');
            $table->timestamp('active_to');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['partnerable_type', 'partnerable_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
