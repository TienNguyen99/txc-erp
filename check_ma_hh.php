<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orders = \App\Models\Order::select('ma_hh')->whereNotNull('ma_hh')->limit(20)->get();
foreach ($orders as $o) {
    echo $o->ma_hh . "\n";
}
