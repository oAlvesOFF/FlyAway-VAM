<?php
/**
 * Diagnostic tool for Atlantic Star Airways deployment
 * Upload to your server root and access via browser.
 * Delete after fixing issues.
 */
$phpVersion = phpversion();
$errors = [];
$warnings = [];
$success = [];

// PHP Version
if (version_compare($phpVersion, '8.2.0', '<')) {
    $errors[] = "PHP $phpVersion detected. Laravel 12 requires PHP 8.2+. Contact your host to upgrade.";
} else {
    $success[] = "PHP $phpVersion ✓";
}

// Required extensions
$required = ['pdo', 'pdo_mysql', 'mbstring', 'xml', 'curl', 'gd', 'bcmath', 'json', 'openssl', 'tokenizer', 'ctype', 'fileinfo'];
foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        $success[] = "Extension: $ext ✓";
    } else {
        $warnings[] = "Extension: $ext ✗ (may be optional)";
    }
}

// Folders
$paths = [
    '.env' => ['.env file', null],
    'vendor/autoload.php' => ['Composer vendor', 'Run: composer install --no-dev'],
    'storage' => ['Storage dir', null],
    'storage/logs' => ['Storage logs dir', null],
    'storage/framework' => ['Storage framework dir', null],
    'storage/framework/cache' => ['Cache dir', null],
    'storage/framework/sessions' => ['Sessions dir', null],
    'storage/framework/views' => ['Views dir', null],
    'bootstrap/cache' => ['Bootstrap cache dir', null],
    'public/.htaccess' => ['.htaccess in public/', null],
];

foreach ($paths as $path => [$label, $fix]) {
    $full = __DIR__ . '/' . $path;
    if (file_exists($full)) {
        $success[] = "$label ✓";
    } else {
        $errors[] = "$label ✗ - Missing" . ($fix ? ". $fix" : '');
    }
}

// Storage writable
$writablePaths = ['storage', 'storage/logs', 'storage/framework', 'bootstrap/cache'];
foreach ($writablePaths as $p) {
    $full = __DIR__ . '/' . $p;
    if (is_dir($full) && is_writable($full)) {
        $success[] = "Writable: $p ✓";
    } elseif (is_dir($full)) {
        $errors[] = "Permission: $p ✗ - Set 755. Run: chmod -R 755 $p";
    }
}

// .env check
if (file_exists(__DIR__ . '/.env')) {
    $envContent = file_get_contents(__DIR__ . '/.env');
    if (str_contains($envContent, 'APP_KEY=') && !str_contains($envContent, 'APP_KEY=base64:')) {
        $warnings[] = "APP_KEY not set. Run: php artisan key:generate";
    }
    if (!str_contains($envContent, 'DB_DATABASE=')) {
        $warnings[] = "DB_DATABASE not found in .env";
    }
}

// APP_KEY check via Laravel if possible
if (file_exists(__DIR__ . '/vendor/autoload.php') && file_exists(__DIR__ . '/bootstrap/app.php')) {
    try {
        require __DIR__ . '/vendor/autoload.php';
        $app = require_once __DIR__ . '/bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        if (config('app.key')) {
            $success[] = "APP_KEY configured ✓";
        } else {
            $errors[] = "APP_KEY not set. Run: php artisan key:generate";
        }
    } catch (Throwable $e) {
        $warnings[] = "Laravel bootstrap: " . $e->getMessage();
    }
}

// Document root check
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? 'unknown';
$ourRoot = __DIR__;
if ($docRoot !== 'unknown' && realpath($docRoot) === realpath($ourRoot)) {
    $warnings[] = "Document root is the project root (not /public). .htaccess redirect should handle this.";
} elseif ($docRoot !== 'unknown') {
    $success[] = "Document root: " . basename($docRoot) . " ✓";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width">
<title>Atlantic Star Airways - Server Check</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:system-ui,-apple-system,sans-serif; background:#0f172a; color:#e2e8f0; display:flex; justify-content:center; padding:2rem; }
.container { max-width:700px; width:100%; }
h1 { font-size:1.5rem; margin-bottom:.25rem; color:#f1f5f9; }
.subtitle { color:#94a3b8; margin-bottom:.5rem; font-size:.9rem; }
.card { background:#1e293b; border:1px solid #334155; border-radius:12px; padding:1.5rem; margin-bottom:1rem; }
h2 { font-size:1.1rem; margin-bottom:.75rem; }
.success { color:#10b981; }
.warning { color:#f59e0b; }
.error { color:#ef4444; }
ul { list-style:none; }
li { padding:.2rem 0; font-size:.85rem; }
code { background:#0f172a; padding:.15rem .4rem; border-radius:4px; font-size:.82rem; }
.checks { display:grid; gap:.35rem; }
.checks div { padding:.35rem .75rem; border-radius:6px; font-size:.85rem; }
.checks .ok { background:rgba(16,185,129,.1); color:#10b981; }
.checks .fail { background:rgba(239,68,68,.1); color:#ef4444; }
.checks .warn { background:rgba(245,158,11,.1); color:#f59e0b; }
.tip { background:#1e3a5f; border:1px solid #1d4ed8; border-radius:8px; padding:1rem; margin-top:1rem; font-size:.85rem; color:#93c5fd; }
</style>
</head>
<body>
<div class="container">
    <h1>✈️ Atlantic Star Airways</h1>
    <p class="subtitle">Deployment Diagnostic — <?= date('Y-m-d H:i:s') ?></p>

    <div class="card">
        <h2>Server</h2>
        <p style="font-size:.85rem;color:#94a3b8;">
            PHP: <?= $phpVersion ?> | 
            Server: <?= $_SERVER['SERVER_SOFTWARE'] ?? 'unknown' ?> | 
            Doc Root: <?= basename($docRoot) ?>
        </p>
    </div>

    <!-- Checklist -->
    <div class="card">
        <h2>Checklist</h2>
        <div class="checks">
            <?php foreach ($success as $s): ?>
            <div class="ok">✅ <?= htmlspecialchars($s) ?></div>
            <?php endforeach; ?>
            <?php foreach ($warnings as $w): ?>
            <div class="warn">⚠️ <?= htmlspecialchars($w) ?></div>
            <?php endforeach; ?>
            <?php foreach ($errors as $e): ?>
            <div class="fail">❌ <?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Tips -->
    <div class="card">
        <h2>Common Fixes</h2>
        <ul style="font-size:.85rem;color:#94a3b8;">
            <li>1️⃣ <strong>Missing vendor?</strong> Upload the <code>vendor/</code> folder or contact your host to run <code>composer install</code></li>
            <li>2️⃣ <strong>Missing .env?</strong> Copy <code>.env.example</code> to <code>.env</code> and edit database credentials</li>
            <li>3️⃣ <strong>APP_KEY?</strong> Run <code>php artisan key:generate</code> (or ask host to run it)</li>
            <li>4️⃣ <strong>Storage permissions?</strong> Set <code>chmod -R 755 storage bootstrap/cache</code></li>
            <li>5️⃣ <strong>Document root?</strong> In cPanel, set document root to <code>/public</code> inside your project folder</li>
            <li>6️⃣ <strong>PHP version?</strong> In cPanel → Select PHP Version → Choose PHP 8.2</li>
        </ul>
    </div>

    <?php if ($errors): ?>
    <div class="card" style="border-color:#ef4444;">
        <h2 style="color:#ef4444;">❌ Fix these errors first</h2>
        <ol style="font-size:.85rem;color:#fca5a5;padding-left:1.25rem;">
            <?php foreach ($errors as $e): ?>
            <li style="padding:.15rem 0;"><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ol>
    </div>
    <?php endif; ?>

    <?php if (empty($errors)): ?>
    <div class="tip">
        ✅ Tudo OK! <strong>Delete este arquivo (check.php)</strong> e acesse <a href="/login" style="color:#60a5fa;">/login</a>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
