<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip for SQLite as it doesn't support ENUM or MODIFY
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        
        // For MySQL, we need to modify the enum column
        // Since MySQL doesn't support adding values to enum directly,
        // we'll alter it to include FAILED
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('PENDING', 'ACCEPTED', 'CONFIRMED', 'REJECTED', 'CANCELED', 'EXPIRED', 'COMPLETED', 'FAILED') DEFAULT 'PENDING'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip for SQLite as it doesn't support ENUM or MODIFY
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        
        // Remove FAILED from enum
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM('PENDING', 'ACCEPTED', 'CONFIRMED', 'REJECTED', 'CANCELED', 'EXPIRED', 'COMPLETED') DEFAULT 'PENDING'");
    }
};
