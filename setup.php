<?php
/**
 * Cria as pastas necessárias para o Laravel
 * Delete após executar.
 */
$dirs = [
    'storage/logs',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'bootstrap/cache',
];

$htaccess = __DIR__ . '/public/.htaccess';
if (!file_exists($htaccess)) {
    file_put_contents($htaccess, '<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    RewriteCond %{HTTP:x-xsrf-token} .
    RewriteRule .* - [E=HTTP_X_XSRF_TOKEN:%{HTTP:X-XSRF-Token}]

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
    echo "✅ public/.htaccess criado\n";
}

$gitkeep = '<?php // ' . date('Y-m-d');

foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
        echo "✅ $dir criado\n";
    } else {
        echo "⏭️ $dir já existe\n";
    }
    // Cria arquivo gitkeep para não ficar vazio
    file_put_contents($path . '/.gitkeep', '');
}

// Tenta criar storage symlink se não existir
$link = __DIR__ . '/public/storage';
$target = __DIR__ . '/storage/app/public';
if (!file_exists($link) && is_dir(dirname($target))) {
    @symlink($target, $link);
    echo "✅ public/storage symlink criado\n";
}

echo "\n📌 Agora faça upload da pasta vendor/ via FTP\n";
echo "📌 Depois acesse: " . ($_SERVER['HTTP_HOST'] ?? 'seu-site') . "/install.php\n";
