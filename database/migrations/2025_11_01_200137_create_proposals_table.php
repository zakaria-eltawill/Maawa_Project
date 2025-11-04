<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('owner_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('property_id')->nullable()->constrained('properties')->onDelete('cascade');
            $table->enum('type', ['ADD', 'EDIT', 'DELETE']);
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->text('notes')->nullable();
            $table->json('payload')->nullable();
            $table->integer('version')->nullable();
            $table->string('reason')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
            
            $table->index(['owner_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
