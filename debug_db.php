<?php

use App\Models\User;
use App\Models\Employee;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- USERS ---\n";
foreach (User::withTrashed()->get() as $u) {
    echo "[ID: {$u->id}] {$u->name} ({$u->email}) Role: {$u->role} | Deleted: " . ($u->deleted_at ? 'YES' : 'NO') . "\n";
}

echo "\n--- EMPLOYEES ---\n";
foreach (Employee::withTrashed()->with('user')->get() as $e) {
    echo "[ID: {$e->id}] {$e->first_name} {$e->last_name} | UserID: {$e->user_id} | Deleted: " . ($e->deleted_at ? 'YES' : 'NO') . "\n";
}
