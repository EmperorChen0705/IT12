<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Item;

$items = Item::all(['name', 'quantity', 'unit']);

echo "name | quantity | unit\n";
foreach ($items as $item) {
    echo "{$item->name} | {$item->quantity} | {$item->unit}\n";
}
