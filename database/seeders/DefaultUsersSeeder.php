<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DefaultUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin',
                'password' => 'Admin123!',
                'role'     => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name'     => 'Employee One',
                'password' => 'Employee123!',
                'role'     => 'employee',
            ]
        );
    }
}