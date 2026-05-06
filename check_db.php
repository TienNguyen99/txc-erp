<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = \Illuminate\Support\Facades\Schema::getAllTables();
foreach ($tables as $t) {
    $tname = (array)$t;
    echo array_values($tname)[0] . "\n";
}
