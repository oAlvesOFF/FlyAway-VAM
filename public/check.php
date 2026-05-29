<?php
/**
 * Diagnostic - Coloque em /public/
 * Acesse: seusite.com/check.php
 */
$root = dirname(__DIR__);
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
$errors = [];
$warnings = [];
$success = [];

function status($label, $ok, $fix = '') {
    global $success, $warnings, $errors;
    if ($ok) $success[] = $label;
    elseif ($fix) $warnings[] = "$label - $fix";
    else $errors[] = $label;
}

status("PHP " . phpversion(), version_compare(PHP_VERSION, '8.2', '>='), 'Upgrade to PHP 8.2+');

$checks = [
    '.env' => [file_exists("$root/.env"), 'Copy .env.example to .env'],
    'vendor/autoload.php' => [file_exists("$root/vendor/autoload.php"), 'Upload vendor/ folder or run "composer install"'],
    'storage' => [is_dir("$root/storage"), 'Create storage/'],
    'storage/logs' => [is_dir("$root/storage/logs"), 'Run setup.php'],
    'storage/framework/cache' => [is_dir("$root/storage/framework/cache"), 'Run setup.php'],
    'storage/framework/sessions' => [is_dir("$root/storage/framework/sessions"), 'Run setup.php'],
    'storage/framework/views' => [is_dir("$root/storage/framework/views"), 'Run setup.php'],
    'bootstrap/cache' => [is_dir("$root/bootstrap/cache"), 'Run setup.php'],
    'public/.htaccess' => [file_exists(__DIR__ . '/.htaccess'), 'Run setup.php'],
    'public/index.php' => [file_exists(__DIR__ . '/index.php'), 'Missing index.php'],
];

foreach ($checks as $label => [$ok, $fix]) {
    status($ok ? "$label ✓" : "❌ $label", $ok, $fix);
}

// Storage writable
foreach (['storage', 'storage/logs', 'bootstrap/cache'] as $p) {
    $full = "$root/$p";
    status(is_dir($full) && is_writable($full) ? "Writable $p ✓" : "❌ $p not writable", is_dir($full) && is_writable($full), "chmod -R 755 $p");
}

// Document root
if (realpath($docRoot) === realpath(__DIR__)) {
    $success[] = "Document root points to /public ✓";
} else {
    $warnings[] = "Document root: " . basename($docRoot) . " (should point to /public)";
}

// .env keys
if (file_exists("$root/.env")) {
    $env = file_get_contents("$root/.env");
    status("APP_KEY " . (str_contains($env, 'APP_KEY=base64:') ? '✓' : '❌'), str_contains($env, 'APP_KEY=base64:'), 'Run php artisan key:generate');
    status("DB_DATABASE " . (str_contains($env, 'DB_DATABASE=') ? '✓' : '❌'), str_contains($env, 'DB_DATABASE='), 'Add DB_DATABASE to .env');
} else {
    status("APP_KEY ✗", false, 'Copy .env.example to .env');
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>Atlantic Star - Diagnostic</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:system-ui,sans-serif; background:#0f172a; color:#e2e8f0; display:flex; justify-content:center; padding:2rem; }
.container { max-width:650px; width:100%; }
h1 { font-size:1.5rem; color:#f1f5f9; margin-bottom:.25rem; }
.sub { color:#94a3b8; margin-bottom:1.5rem; font-size:.9rem; }
.card { background:#1e293b; border:1px solid #334155; border-radius:12px; padding:1.5rem; margin-bottom:1rem; }
h2 { font-size:1.1rem; margin-bottom:.75rem; }
.ok { color:#10b981; }
.warn { color:#f59e0b; }
.fail { color:#ef4444; }
ul { list-style:none; }
li { padding:.25rem 0; font-size:.85rem; }
code { background:#0f172a; padding:.15rem .4rem; border-radius:4px; }
</style>
</head>
<body>
<div class="container">
    <h1>✈️ Atlantic Star Airways</h1>
    <p class="sub">Server: <?= $_SERVER['SERVER_SOFTWARE'] ?? 'unknown' ?> | Root: <?= basename($root) ?></p>

    <div class="card">
        <h2>Checklist</h2>
        <ul>
            <?php foreach ($success as $s): ?>
            <li class="ok">✅ <?= htmlspecialchars($s) ?></li>
            <?php endforeach; ?>
            <?php foreach ($warnings as $w): ?>
            <li class="warn">⚠️ <?= htmlspecialchars($w) ?></li>
            <?php endforeach; ?>
            <?php foreach ($errors as $e): ?>
            <li class="fail">❌ <?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="card">
        <h2>Next steps</h2>
        <ol style="font-size:.85rem;color:#94a3b8;padding-left:1.25rem;">
            <li style="padding:.15rem 0;">1️⃣ <strong>setup.php</strong> → <code>seusite.com/setup.php</code> (cria pastas)</li>
            <li style="padding:.15rem 0;">2️⃣ <strong>vendor/</strong> → Faça upload via FTP</li>
            <li style="padding:.15rem 0;">3️⃣ <strong>install.php</strong> → <code>seusite.com/install.php</code> (banco)</li>
            <li style="padding:.15rem 0;">4️⃣ Delete <code>check.php</code>, <code>setup.php</code>, <code>install.php</code></li>
        </ol>
    </div>
</div>
</body>
</html>
