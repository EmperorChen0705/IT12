<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceType;

class UpdateServiceTypesSeeder extends Seeder
{
    public function run()
    {
        $types = [
            'Consultation & Design',
            'Customization & Upgrades',
            'Installation',
            'Rental Service',
            'Repair & Maintenance',
        ];

        foreach ($types as $name) {
            ServiceType::firstOrCreate(['name' => $name], ['active' => true]);
        }

        // Optional: Deactivate others not in list? User didn't ask, but "exactly like" might imply exclusively.
        // For safety I won't delete, just ensure these exist.
    }
}
