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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number', 20)->nullable()->after('email');
            $table->string('region', 100)->nullable()->after('phone_number');
        });

        // Update existing users with unique placeholder values
        $users = DB::table('users')->whereNull('phone_number')->get();
        foreach ($users as $index => $user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'phone_number' => '+218910' . str_pad($index, 6, '0', STR_PAD_LEFT),
                    'region' => 'Not Specified',
                ]);
        }

        // Now add the unique constraint and make columns required
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number', 20)->unique()->nullable(false)->change();
            $table->string('region', 100)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'region']);
        });
    }
};
