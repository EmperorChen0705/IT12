<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use Illuminate\Support\Facades\DB;

$bookings = Booking::all();

echo "Existing Bookings:\n";
foreach ($bookings as $b) {
    echo "- {$b->booking_id} | {$b->status} | {$b->preferred_date}\n";
}
