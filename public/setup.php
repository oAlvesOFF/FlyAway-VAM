<?php
/**
 * Creates Laravel storage/cache directories.
 * Access via: seusite.com/setup.php
 * Delete after running.
 */
$root = dirname(__DIR__); // goes up from /public to project root

$dirs = [
    'storage/logs',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/app/public/avatars',
    'bootstrap/cache',
];

$htaccess = __DIR__ . '/.htaccess';
if (!file_exists($htaccess)) {
    file_put_contents($htaccess, '<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    RewriteCond %{HTTP:x-xsrf-token} .
    RewriteRule .* - [E=HTTP_X_XSRF_TOKEN:%{HTTP:X-Xsrf-Token}]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

<IfModule mod_headers.c>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

<FilesMatch "\.(env|json|lock|log|yml|yaml|md|gitignore|gitattributes)$">
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
    <IfModule !mod_authz_core.c>
        Deny from all
    </IfModule>
</FilesMatch>
');
    echo "<div style='color:#10b981'>✅ public/.htaccess criado</div>\n";
}

foreach ($dirs as $dir) {
    $path = "$root/$dir";
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "<div style='color:#10b981'>✅ $dir criado</div>\n";
    } else {
        echo "<div style='color:#94a3b8'>⏭️ $dir já existe</div>\n";
    }
    file_put_contents("$path/.gitkeep", '');
}

// storage link
$link = __DIR__ . '/storage';
$target = "$root/storage/app/public";
if (!file_exists($link) && is_dir(dirname($target))) {
    @symlink($target, $link);
    echo "<div style='color:#10b981'>✅ public/storage symlink criado</div>\n";
}

echo "<hr style='border-color:#334155;margin:1rem 0'>";
echo "<div style='color:#f59e0b'>⚠️ Delete este arquivo (setup.php) após confirmar.</div>\n";
echo "<div style='color:#e2e8f0'>📌 Próximo: acesse <strong>install.php</strong> para criar o banco</div>\n";
