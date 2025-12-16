<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Item;

$items = Item::all();
$count = 0;

foreach ($items as $item) {
    // If unit appears to be a number (numeric string), reset it to 'pcs'
    if (is_numeric($item->unit)) {
        echo "Fixing Item: {$item->name} (Unit was '{$item->unit}') -> 'pcs'\n";
        $item->unit = 'pcs';
        $item->save();
        $count++;
    }
}

echo "Fixed {$count} items.\n";
