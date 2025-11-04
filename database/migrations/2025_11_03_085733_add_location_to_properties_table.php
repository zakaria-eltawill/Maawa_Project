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
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'location_lat')) {
                $table->decimal('location_lat', 10, 7)->nullable()->after('price');
            }
            if (!Schema::hasColumn('properties', 'location_lng')) {
                $table->decimal('location_lng', 10, 7)->nullable()->after('location_lat');
            }

            // Composite index for future geo-search (city + coordinates)
            $table->index(['city', 'location_lat', 'location_lng'], 'properties_city_lat_lng_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'location_lat')) {
                $table->dropColumn('location_lat');
            }
            if (Schema::hasColumn('properties', 'location_lng')) {
                $table->dropColumn('location_lng');
            }
            $table->dropIndex('properties_city_lat_lng_index');
        });
    }
};
