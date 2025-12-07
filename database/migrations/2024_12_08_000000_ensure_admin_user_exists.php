<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if admin already exists to avoid duplicates or errors
        if (!User::where('email', 'admin@subwfour.com')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@subwfour.com',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: Remove the admin user if rolling back
        // User::where('email', 'admin@subwfour.com')->delete();
    }
};
