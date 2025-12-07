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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_manager')->default(false)->after('role');
            $table->timestamp('elevated_until')->nullable()->after('is_manager');
            $table->foreignId('elevated_by')->nullable()->after('elevated_until')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['elevated_by']);
            $table->dropColumn(['is_manager', 'elevated_until', 'elevated_by']);
        });
    }
};
