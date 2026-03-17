<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $result = DB::connection('oracle')->select('SELECT 1 as TEST_COL FROM DUAL');
    echo "Connexion Oracle OK\n";
    print_r($result);
} catch (\Exception $e) {
    echo "ERREUR Oracle: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
}
