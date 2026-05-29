<?php

require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\SimbriefService();
// We can use a test simbrief username or just read the code to see what files exist.
$response = \Illuminate\Support\Facades\Http::get('https://www.simbrief.com/api/xml.fetcher.php?username=noxxr&json=1');
if ($response->successful()) {
    $data = $response->json();
    $files = $data['files'] ?? [];
    $prefile = $data['prefile'] ?? [];
    $fms = $data['fms_downloads'] ?? [];
    echo "Files keys: " . implode(', ', array_keys($files)) . "\n";
    echo "Prefile keys: " . implode(', ', array_keys($prefile)) . "\n";
    echo "FMS keys: " . implode(', ', array_keys($fms)) . "\n";
    file_put_contents('scratch/simbrief_dump.json', json_encode(['files' => $files, 'prefile' => $prefile, 'fms' => $fms], JSON_PRETTY_PRINT));
} else {
    echo "Failed to fetch";
}
