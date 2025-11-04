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
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignUuid('property_id')->constrained('properties')->onDelete('cascade');
            $table->foreignUuid('tenant_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating');
            $table->string('comment', 500)->nullable();
            $table->timestamps();
            
            $table->unique('booking_id');
            $table->index('property_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
