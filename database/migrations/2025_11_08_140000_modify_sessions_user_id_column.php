<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip for SQLite as it doesn't support MODIFY
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        
        DB::statement("ALTER TABLE sessions MODIFY user_id CHAR(36) NULL");
    }

    public function down(): void
    {
        // Skip for SQLite as it doesn't support MODIFY
        if (DB::getDriverName() === 'sqlite') {
            return;
        }
        
        DB::statement("ALTER TABLE sessions MODIFY user_id BIGINT UNSIGNED NULL");
    }
};
