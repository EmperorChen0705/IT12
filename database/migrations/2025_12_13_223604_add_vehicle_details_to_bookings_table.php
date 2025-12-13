<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('vehicle_make', 50)->nullable()->after('customer_name');
            $table->string('vehicle_model', 50)->nullable()->after('vehicle_make');
            $table->string('plate_number', 20)->nullable()->after('vehicle_model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['vehicle_make', 'vehicle_model', 'plate_number']);
        });
    }
};
