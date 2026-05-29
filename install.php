<?php
/**
 * Atlantic Star Airways - Database Installer
 * 
 * Upload this file to your shared hosting root, access it via browser,
 * then DELETE it after installation.
 * 
 * Configure your database in the $config array below or in .env.
 * Default admin: admin@atlanticstar.aero / password
 */

// Simple env helper (standalone - no Laravel)
function env($key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// ------------------------------------------------
// CONFIGURATION - Edit these if not using .env
// ------------------------------------------------
$config = [
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'flyaway_vam'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
];

$adminEmail = 'admin@atlanticstar.aero';
$adminPassword = 'password';
$pilotEmail = 'pilot@atlanticstar.aero';
$pilotPassword = 'password';

// ------------------------------------------------
// BOOTSTRAP
// ------------------------------------------------
// Look for .env in current dir or parent dir (for /public/ placement)
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    $envPath = dirname(__DIR__) . '/.env';
}
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = explode('=', $line, 2) + [null, null];
        if ($key && $value !== null) {
            $_ENV[$key] = trim($value, '"\'');
        }
    }
    $config['host'] = $_ENV['DB_HOST'] ?? $config['host'];
    $config['port'] = $_ENV['DB_PORT'] ?? $config['port'];
    $config['database'] = $_ENV['DB_DATABASE'] ?? $config['database'];
    $config['username'] = $_ENV['DB_USERNAME'] ?? $config['username'];
    $config['password'] = $_ENV['DB_PASSWORD'] ?? $config['password'];
}

$charset = 'utf8mb4';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};charset={$charset}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['database']}` DEFAULT CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci");
    $pdo->exec("USE `{$config['database']}`");
} catch (PDOException $e) {
    die("<h2>Connection Error</h2><p>{$e->getMessage()}</p>");
}

$run = $_POST['run'] ?? false;
$step = 0;
$errors = [];
$success = [];

function runSQL(PDO $pdo, string $sql): void {
    global $step, $errors, $success;
    $step++;
    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        $errors[] = "Step {$step}: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Atlantic Star Airways - Installer</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: system-ui, -apple-system, sans-serif; background:#0f172a; color:#e2e8f0; display:flex; justify-content:center; padding:2rem; }
.container { max-width:800px; width:100%; }
h1 { font-size:1.5rem; margin-bottom:.5rem; color:#f1f5f9; }
h2 { font-size:1.1rem; margin:1.5rem 0 .75rem; color:#e11d48; }
.subtitle { color:#94a3b8; margin-bottom:1.5rem; }
.card { background:#1e293b; border:1px solid #334155; border-radius:12px; padding:1.5rem; margin-bottom:1rem; }
.btn { background:#e11d48; color:#fff; border:none; padding:.75rem 2rem; border-radius:8px; font-size:1rem; cursor:pointer; }
.btn:hover { background:#be123c; }
.btn:disabled { background:#475569; cursor:not-allowed; }
.success { color:#10b981; }
.error { color:#ef4444; }
ul { list-style:none; }
li { padding:.25rem 0; font-size:.9rem; }
.info { color:#94a3b8; font-size:.85rem; margin-top:.5rem; }
code { background:#0f172a; padding:.15rem .4rem; border-radius:4px; font-size:.85rem; color:#e2e8f0; }
</style>
</head>
<body>
<div class="container">
    <h1>✈️ Atlantic Star Airways</h1>
    <p class="subtitle">Database Installer</p>

    <div class="card">
        <table style="width:100%; font-size:.9rem;">
            <tr><td style="padding:.25rem; color:#94a3b8;">Host:</td><td><?= htmlspecialchars($config['host']) ?></td></tr>
            <tr><td style="padding:.25rem; color:#94a3b8;">Database:</td><td><?= htmlspecialchars($config['database']) ?></td></tr>
            <tr><td style="padding:.25rem; color:#94a3b8;">User:</td><td><?= htmlspecialchars($config['username']) ?></td></tr>
        </table>
    </div>

    <?php if (!$run): ?>
    <form method="post">
        <input type="hidden" name="run" value="1">
        <p class="info" style="margin-bottom:1rem;">This will create all tables and seed default data. <strong>Delete this file after installation.</strong></p>
        <button type="submit" class="btn">Install Database</button>
    </form>
    <?php else: ?>
    <div class="card">
        <h2>Progress</h2>
        <ul>
        <?php
        // ---- 1. Cache ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `cache` (`key` VARCHAR(255) NOT NULL PRIMARY KEY, `value` MEDIUMTEXT NOT NULL, `expiration` INT NOT NULL, INDEX `expiration_idx` (`expiration`)) ENGINE=InnoDB DEFAULT CHARSET={$charset}");
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `cache_locks` (`key` VARCHAR(255) NOT NULL PRIMARY KEY, `owner` VARCHAR(255) NOT NULL, `expiration` INT NOT NULL, INDEX `expiration_idx` (`expiration`)) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 2. Sessions ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `sessions` (`id` VARCHAR(255) NOT NULL PRIMARY KEY, `user_id` BIGINT UNSIGNED DEFAULT NULL, `ip_address` VARCHAR(45) DEFAULT NULL, `user_agent` TEXT DEFAULT NULL, `payload` LONGTEXT NOT NULL, `last_activity` INT NOT NULL, INDEX `last_activity_idx` (`last_activity`)) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 3. Jobs ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `jobs` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `queue` VARCHAR(255) NOT NULL, `payload` LONGTEXT NOT NULL, `attempts` TINYINT UNSIGNED NOT NULL, `reserved_at` INT UNSIGNED DEFAULT NULL, `available_at` INT UNSIGNED NOT NULL, `created_at` INT UNSIGNED NOT NULL, INDEX `jobs_queue_index` (`queue`)) ENGINE=InnoDB DEFAULT CHARSET={$charset}");
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `job_batches` (`id` VARCHAR(255) NOT NULL PRIMARY KEY, `name` VARCHAR(255) NOT NULL, `total_jobs` INT NOT NULL, `pending_jobs` INT NOT NULL, `failed_jobs` INT NOT NULL, `failed_job_ids` LONGTEXT NOT NULL, `options` MEDIUMTEXT DEFAULT NULL, `cancelled_at` INT DEFAULT NULL, `created_at` INT NOT NULL, `finished_at` INT DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET={$charset}");
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `failed_jobs` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `uuid` VARCHAR(255) NOT NULL UNIQUE, `connection` TEXT NOT NULL, `queue` TEXT NOT NULL, `payload` LONGTEXT NOT NULL, `exception` LONGTEXT NOT NULL, `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 4. Password resets ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `password_reset_tokens` (`email` VARCHAR(255) NOT NULL PRIMARY KEY, `token` VARCHAR(255) NOT NULL, `created_at` TIMESTAMP NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 5. Ranks ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `ranks` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) NOT NULL, `minimum_hours` INT NOT NULL DEFAULT 0, `image` VARCHAR(255) DEFAULT NULL, `allowed_categories` TEXT DEFAULT NULL, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 6. Roles (needed before users) ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `roles` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) NOT NULL, `slug` VARCHAR(255) NOT NULL UNIQUE, `description` TEXT DEFAULT NULL, `is_staff` TINYINT(1) NOT NULL DEFAULT 0, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 7. Permissions ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `permissions` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) NOT NULL, `slug` VARCHAR(255) NOT NULL UNIQUE, `group` VARCHAR(255) DEFAULT NULL, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET={$charset}");
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `permission_role` (`permission_id` BIGINT UNSIGNED NOT NULL, `role_id` BIGINT UNSIGNED NOT NULL, PRIMARY KEY (`permission_id`, `role_id`), CONSTRAINT `fk_perm_role_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE, CONSTRAINT `fk_perm_role_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 8. Users ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `users` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) NOT NULL, `email` VARCHAR(255) NOT NULL UNIQUE, `password` VARCHAR(255) NOT NULL, `pilot_id` VARCHAR(20) DEFAULT NULL UNIQUE, `rank_id` BIGINT UNSIGNED DEFAULT NULL, `role_id` BIGINT UNSIGNED DEFAULT NULL, `total_hours` DECIMAL(10,2) NOT NULL DEFAULT 0.00, `total_flights` INT NOT NULL DEFAULT 0, `last_location` VARCHAR(4) DEFAULT 'YSSY', `status` VARCHAR(20) NOT NULL DEFAULT 'active', `suspension_reason` TEXT DEFAULT NULL, `is_admin` TINYINT(1) NOT NULL DEFAULT 0, `simbrief_username` VARCHAR(255) DEFAULT NULL, `avatar` VARCHAR(255) DEFAULT NULL, `email_verified_at` TIMESTAMP NULL DEFAULT NULL, `remember_token` VARCHAR(100) DEFAULT NULL, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL, INDEX `rank_id_idx` (`rank_id`), CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 9. Aircraft ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `aircraft` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `registration` VARCHAR(20) NOT NULL UNIQUE, `icao` VARCHAR(4) NOT NULL, `name` VARCHAR(255) NOT NULL, `location` VARCHAR(4) DEFAULT 'YSSY', `status` VARCHAR(20) NOT NULL DEFAULT 'active', `category` VARCHAR(50) NOT NULL, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 10. Schedules ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `schedules` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `flight_number` VARCHAR(20) NOT NULL, `departure` VARCHAR(4) NOT NULL, `arrival` VARCHAR(4) NOT NULL, `route` TEXT NOT NULL, `aircraft_type` VARCHAR(10) NOT NULL, `flight_time` DECIMAL(5,2) NOT NULL, `departure_time` VARCHAR(10) DEFAULT '12:00', `altitude` INT DEFAULT 30000, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 11. Bids ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `bids` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT UNSIGNED NOT NULL, `schedule_id` BIGINT UNSIGNED NOT NULL, `aircraft_id` BIGINT UNSIGNED NOT NULL, `simbrief_ofp` LONGTEXT DEFAULT NULL, `simbrief_xml` LONGTEXT DEFAULT NULL, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL, CONSTRAINT `fk_bids_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE, CONSTRAINT `fk_bids_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules`(`id`) ON DELETE CASCADE, CONSTRAINT `fk_bids_aircraft` FOREIGN KEY (`aircraft_id`) REFERENCES `aircraft`(`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 12. PIREPs ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `pireps` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT UNSIGNED NOT NULL, `flight_number` VARCHAR(20) NOT NULL, `departure` VARCHAR(4) NOT NULL, `arrival` VARCHAR(4) NOT NULL, `aircraft_registration` VARCHAR(20) NOT NULL, `aircraft_icao` VARCHAR(4) NOT NULL, `flight_time` DECIMAL(5,2) NOT NULL, `landing_rate` INT NOT NULL DEFAULT 0, `score` INT NOT NULL DEFAULT 100, `route` TEXT DEFAULT NULL, `status` VARCHAR(20) NOT NULL DEFAULT 'pending', `log` LONGTEXT DEFAULT NULL, `rejection_reason` TEXT DEFAULT NULL, `submitted_at` TIMESTAMP NULL DEFAULT NULL, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL, CONSTRAINT `fk_pireps_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 13. Active Flights ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `active_flights` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `flight_number` VARCHAR(20) NOT NULL, `aircraft_registration` VARCHAR(20) NOT NULL, `aircraft_icao` VARCHAR(4) NOT NULL, `aircraft_type` VARCHAR(10) NOT NULL, `departure` VARCHAR(4) NOT NULL, `arrival` VARCHAR(4) NOT NULL, `departure_lat` DECIMAL(10,6) DEFAULT NULL, `departure_lng` DECIMAL(10,6) DEFAULT NULL, `arrival_lat` DECIMAL(10,6) DEFAULT NULL, `arrival_lng` DECIMAL(10,6) DEFAULT NULL, `current_lat` DECIMAL(10,6) DEFAULT NULL, `current_lng` DECIMAL(10,6) DEFAULT NULL, `heading` INT NOT NULL DEFAULT 0, `altitude` INT NOT NULL DEFAULT 0, `ground_speed` INT NOT NULL DEFAULT 0, `phase` VARCHAR(20) NOT NULL DEFAULT 'preflight', `status` VARCHAR(20) NOT NULL DEFAULT 'active', `started_at` TIMESTAMP NULL DEFAULT NULL, `position_updated_at` TIMESTAMP NULL DEFAULT NULL, `ended_at` TIMESTAMP NULL DEFAULT NULL, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 14. News ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `activity_logs` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT UNSIGNED DEFAULT NULL, `action` VARCHAR(100) NOT NULL, `subject_type` VARCHAR(100) DEFAULT NULL, `subject_id` BIGINT UNSIGNED DEFAULT NULL, `description` TEXT DEFAULT NULL, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL, INDEX `activity_logs_subject_idx` (`subject_type`, `subject_id`), CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `news` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `title` VARCHAR(255) NOT NULL, `slug` VARCHAR(255) NOT NULL UNIQUE, `excerpt` TEXT DEFAULT NULL, `content` LONGTEXT NOT NULL, `author_id` BIGINT UNSIGNED DEFAULT NULL, `published_at` TIMESTAMP NULL DEFAULT NULL, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL, CONSTRAINT `fk_news_author` FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET={$charset}");
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `custom_pages` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `title` VARCHAR(255) NOT NULL, `slug` VARCHAR(255) NOT NULL UNIQUE, `content` LONGTEXT NOT NULL, `published` TINYINT(1) NOT NULL DEFAULT 1, `order` INT NOT NULL DEFAULT 0, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET={$charset}");
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `airports` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `icao` VARCHAR(4) NOT NULL UNIQUE, `name` VARCHAR(255) NOT NULL, `city` VARCHAR(255) NOT NULL, `country` VARCHAR(255) NOT NULL, `lat` DECIMAL(10,6) NOT NULL, `lng` DECIMAL(10,6) NOT NULL, `elevation` INT DEFAULT NULL, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 15. Notifications ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `notifications` (`id` CHAR(36) NOT NULL PRIMARY KEY, `type` VARCHAR(255) NOT NULL, `notifiable_type` VARCHAR(255) NOT NULL, `notifiable_id` BIGINT UNSIGNED NOT NULL, `data` TEXT NOT NULL, `read_at` TIMESTAMP NULL DEFAULT NULL, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL, INDEX `notif_index` (`notifiable_type`, `notifiable_id`)) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 16. Settings ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `settings` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `key` VARCHAR(255) NOT NULL UNIQUE, `value` TEXT DEFAULT NULL, `type` VARCHAR(50) NOT NULL DEFAULT 'string', `label` VARCHAR(255) DEFAULT NULL, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 17. Achievements ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `achievements` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) NOT NULL, `slug` VARCHAR(255) NOT NULL UNIQUE, `description` TEXT NOT NULL, `icon` VARCHAR(50) DEFAULT NULL, `category` VARCHAR(50) NOT NULL DEFAULT 'general', `threshold` INT UNSIGNED NOT NULL DEFAULT 0, `metric` VARCHAR(50) NOT NULL, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET={$charset}");
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `achievement_user` (`achievement_id` BIGINT UNSIGNED NOT NULL, `user_id` BIGINT UNSIGNED NOT NULL, `unlocked_at` TIMESTAMP NOT NULL, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL, PRIMARY KEY (`achievement_id`, `user_id`), CONSTRAINT `fk_ach_ach` FOREIGN KEY (`achievement_id`) REFERENCES `achievements`(`id`) ON DELETE CASCADE, CONSTRAINT `fk_ach_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- 18. Tours ----
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `tours` (`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, `name` VARCHAR(255) NOT NULL, `slug` VARCHAR(255) NOT NULL UNIQUE, `description` TEXT DEFAULT NULL, `category` VARCHAR(255) NOT NULL, `waypoints` JSON DEFAULT NULL, `order` INT UNSIGNED NOT NULL DEFAULT 0, `is_active` TINYINT(1) NOT NULL DEFAULT 1, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL) ENGINE=InnoDB DEFAULT CHARSET={$charset}");
        runSQL($pdo, "CREATE TABLE IF NOT EXISTS `tour_user` (`tour_id` BIGINT UNSIGNED NOT NULL, `user_id` BIGINT UNSIGNED NOT NULL, `progress` INT UNSIGNED NOT NULL DEFAULT 0, `completed` TINYINT(1) NOT NULL DEFAULT 0, `completed_at` TIMESTAMP NULL DEFAULT NULL, `created_at` TIMESTAMP NULL DEFAULT NULL, `updated_at` TIMESTAMP NULL DEFAULT NULL, PRIMARY KEY (`tour_id`, `user_id`), CONSTRAINT `fk_tour_tour` FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`) ON DELETE CASCADE, CONSTRAINT `fk_tour_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET={$charset}");

        // ---- SEED: Ranks ----
        runSQL($pdo, "INSERT IGNORE INTO `ranks` (`id`, `name`, `minimum_hours`, `allowed_categories`) VALUES
            (1, 'Second Officer', 0, 'Narrowbody'),
            (2, 'First Officer', 100, 'Narrowbody,Widebody'),
            (3, 'Captain', 500, 'Narrowbody,Widebody'),
            (4, 'Senior Captain', 1500, 'Narrowbody,Widebody,Heavy'),
            (5, 'Chief Pilot', 5000, 'Narrowbody,Widebody,Heavy')");

        // ---- SEED: Permissions ----
        runSQL($pdo, "INSERT IGNORE INTO `permissions` (`id`, `name`, `slug`, `group`) VALUES
            (1, 'View Staff Center', 'staff.access', 'Staff'),
            (2, 'Manage Roles', 'roles.manage', 'Staff'),
            (3, 'View PIREPs', 'pireps.view', 'PIREPs'),
            (4, 'Approve PIREPs', 'pireps.approve', 'PIREPs'),
            (5, 'Reject PIREPs', 'pireps.reject', 'PIREPs'),
            (6, 'View Pilots', 'pilots.view', 'Pilots'),
            (7, 'Manage Pilots', 'pilots.manage', 'Pilots'),
            (8, 'View Fleet', 'fleet.view', 'Fleet'),
            (9, 'Manage Fleet', 'fleet.manage', 'Fleet'),
            (10, 'View Schedules', 'schedules.view', 'Schedules'),
            (11, 'Manage Schedules', 'schedules.manage', 'Schedules'),
            (12, 'Manage News', 'news.manage', 'News'),
            (13, 'Manage Settings', 'settings.manage', 'Settings')");

        // ---- SEED: Roles ----
        runSQL($pdo, "INSERT IGNORE INTO `roles` (`id`, `name`, `slug`, `description`, `is_staff`) VALUES
            (1, 'Chief Pilot', 'chief-pilot', 'Senior pilot with oversight responsibilities', 1),
            (2, 'Moderator', 'moderator', 'Can manage PIREPs and pilots', 1)");

        // ---- SEED: Role-Permission ----
        runSQL($pdo, "INSERT IGNORE INTO `permission_role` (`permission_id`, `role_id`) VALUES
            (1,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(10,1),
            (1,2),(3,2),(4,2),(5,2),(6,2)");

        // ---- SEED: Aircraft ----
        runSQL($pdo, "INSERT IGNORE INTO `aircraft` (`id`, `registration`, `icao`, `name`, `location`, `status`, `category`) VALUES
            (1, 'AS-B738', 'B738', 'Boeing 737-800', 'YSSY', 'active', 'Narrowbody'),
            (2, 'AS-B73A', 'B738', 'Boeing 737-800', 'YMML', 'active', 'Narrowbody'),
            (3, 'AS-A320', 'A320', 'Airbus A320-200', 'YBBN', 'active', 'Narrowbody'),
            (4, 'AS-A32A', 'A320', 'Airbus A320-200', 'YSSY', 'active', 'Narrowbody'),
            (5, 'AS-B789', 'B789', 'Boeing 787-9 Dreamliner', 'YSSY', 'active', 'Widebody'),
            (6, 'AS-B78A', 'B789', 'Boeing 787-9 Dreamliner', 'NZAA', 'active', 'Widebody'),
            (7, 'AS-B772', 'B772', 'Boeing 777-200ER', 'WSSS', 'active', 'Widebody'),
            (8, 'AS-A388', 'A388', 'Airbus A380-800', 'EGLL', 'active', 'Heavy')");

        // ---- SEED: Schedules ----
        runSQL($pdo, "INSERT IGNORE INTO `schedules` (`id`, `flight_number`, `departure`, `arrival`, `route`, `aircraft_type`, `flight_time`, `departure_time`, `altitude`) VALUES
            (1, 'ASA001', 'YSSY', 'YMML', 'SYD to MEL', 'B738', 1.50, '06:00', 36000),
            (2, 'ASA002', 'YMML', 'YSSY', 'MEL to SYD', 'B738', 1.50, '08:00', 36000),
            (3, 'ASA003', 'YSSY', 'YBBN', 'SYD to BNE', 'B738', 1.75, '07:00', 34000),
            (4, 'ASA004', 'YBBN', 'YSSY', 'BNE to SYD', 'B738', 1.75, '09:00', 34000),
            (5, 'ASA005', 'YSSY', 'YPPH', 'SYD to PER', 'A320', 4.50, '10:00', 38000),
            (6, 'ASA006', 'YPPH', 'YSSY', 'PER to SYD', 'A320', 4.50, '15:00', 38000),
            (7, 'ASA007', 'YMML', 'YBCS', 'MEL to CNS', 'B738', 3.50, '09:00', 36000),
            (8, 'ASA008', 'YBCS', 'YMML', 'CNS to MEL', 'B738', 3.00, '13:00', 36000),
            (9, 'ASA009', 'YSSY', 'NZAA', 'SYD to AKL', 'B789', 3.75, '08:00', 38000),
            (10, 'ASA010', 'NZAA', 'YSSY', 'AKL to SYD', 'B789', 3.75, '12:00', 38000),
            (11, 'ASA011', 'YSSY', 'WSSS', 'SYD to SIN', 'B789', 8.00, '22:00', 40000),
            (12, 'ASA012', 'WSSS', 'YSSY', 'SIN to SYD', 'B789', 8.00, '23:00', 40000),
            (13, 'ASA013', 'YSSY', 'RJTT', 'SYD to NRT', 'B772', 10.50, '21:00', 40000),
            (14, 'ASA014', 'RJTT', 'YSSY', 'NRT to SYD', 'B772', 10.50, '21:00', 40000),
            (15, 'ASA015', 'YSSY', 'EGLL', 'SYD to LHR', 'A388', 24.00, '18:00', 41000)");

        // ---- SEED: Settings ----
        runSQL($pdo, "INSERT IGNORE INTO `settings` (`id`, `key`, `value`, `type`, `label`) VALUES
            (1, 'va_name', 'Atlantic Star Airways', 'text', 'Airline Name'),
            (2, 'va_callsign', 'ATLANTIC', 'text', 'Callsign'),
            (3, 'va_home', 'YSSY', 'text', 'Home Airport (ICAO)'),
            (4, 'va_description', 'A premium Next-Gen Virtual Airline based in Sydney, Australia.', 'textarea', 'Airline Description'),
            (5, 'registration_open', 'true', 'boolean', 'Allow Public Registration'),
            (6, 'mqtt_enabled', 'false', 'boolean', 'MQTT Bridge Enabled'),
            (7, 'mqtt_host', '127.0.0.1', 'text', 'MQTT Broker Host'),
            (8, 'mqtt_port', '1883', 'text', 'MQTT Broker Port'),
            (9, 'mqtt_username', '', 'text', 'MQTT Username'),
            (10, 'mqtt_password', '', 'text', 'MQTT Password'),
            (11, 'auto_approve_threshold', '90', 'string', 'Auto-Approve Score Threshold'),
            (12, 'discord_webhook_url', '', 'string', 'Discord Webhook URL')");

        // ---- SEED: Achievements ----
        runSQL($pdo, "INSERT IGNORE INTO `achievements` (`id`, `name`, `slug`, `description`, `icon`, `category`, `threshold`, `metric`) VALUES
            (1, 'First Flight', 'first-flight', 'File your first PIREP', '🛩️', 'flights', 1, 'total_flights'),
            (2, 'Weekend Warrior', 'weekend-warrior', 'Complete 10 flights', '✈️', 'flights', 10, 'total_flights'),
            (3, 'Century Club', 'century-club', 'Complete 100 flights', '🏆', 'flights', 100, 'total_flights'),
            (4, 'Sky King', 'sky-king', 'Complete 500 flights', '👑', 'flights', 500, 'total_flights'),
            (5, 'First Hours', 'first-hours', 'Log 10 flight hours', '⏱️', 'hours', 10, 'total_hours'),
            (6, 'Century Hours', 'century-hours', 'Log 100 flight hours', '🕐', 'hours', 100, 'total_hours'),
            (7, 'Veteran Pilot', 'veteran-pilot', 'Log 1,000 flight hours', '⭐', 'hours', 1000, 'total_hours'),
            (8, 'Butter Landing', 'butter-landing', 'Score 5 perfect landings (score 100)', '🦋', 'skill', 5, 'perfect_landings'),
            (9, 'Silk Touch', 'silk-touch', 'Score 25 perfect landings', '✨', 'skill', 25, 'perfect_landings'),
            (10, 'Route Explorer', 'route-explorer', 'Fly 10 different routes', '🗺️', 'exploration', 10, 'routes_flown'),
            (11, 'World Traveler', 'world-traveler', 'Fly 25 different routes', '🌍', 'exploration', 25, 'routes_flown'),
            (12, 'Dedicated Flyer', 'dedicated-flyer', 'File 50 PIREPs', '📋', 'flights', 50, 'pireps_filed')");

        // ---- SEED: Tours ----
        runSQL($pdo, "INSERT IGNORE INTO `tours` (`id`, `name`, `slug`, `description`, `category`, `waypoints`, `order`, `is_active`) VALUES
            (1, 'Australian East Coast', 'east-coast-australia', 'Fly along the beautiful Australian east coast', 'regional', '[\"YBCS\",\"YBBN\",\"YSSY\",\"YMML\"]', 1, 1),
            (2, 'Trans-Tasman', 'trans-tasman', 'Cross the Tasman Sea between Australia and New Zealand', 'regional', '[\"YSSY\",\"NZAA\",\"YMML\",\"NZAA\"]', 2, 1),
            (3, 'Asia Pacific', 'asia-pacific', 'Connect the Pacific region through major hubs', 'international', '[\"YSSY\",\"WSSS\",\"RJTT\",\"YPPH\"]', 3, 1)");

        // ---- SEED: Users ----
        $adminHash = password_hash($adminPassword, PASSWORD_BCRYPT);
        $pilotHash = password_hash($pilotPassword, PASSWORD_BCRYPT);
        $pdo->prepare("INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password`, `pilot_id`, `rank_id`, `total_hours`, `total_flights`, `status`, `is_admin`, `email_verified_at`, `created_at`, `updated_at`) VALUES
            (1, 'Admin User', ?, ?, 'ASR0001', 5, 5200, 50, 'active', 1, NOW(), NOW(), NOW()),
            (2, 'Test Pilot', ?, ?, 'ASR1001', 2, 250, 12, 'active', 0, NOW(), NOW(), NOW())")->execute([$adminEmail, $adminHash, $pilotEmail, $pilotHash]);

        // ---- SEED: Active Flights ----
        runSQL($pdo, "INSERT IGNORE INTO `active_flights` (`id`, `flight_number`, `aircraft_registration`, `aircraft_icao`, `aircraft_type`, `departure`, `arrival`, `departure_lat`, `departure_lng`, `arrival_lat`, `arrival_lng`, `current_lat`, `current_lng`, `heading`, `altitude`, `ground_speed`, `phase`, `status`, `started_at`, `position_updated_at`) VALUES
            (1, 'ASA001', 'AS-B738', 'B738', 'B738', 'YSSY', 'YMML', -33.946111, 151.177222, -37.673333, 144.843333, -34.050000, 150.780000, 180, 1500, 180, 'climb', 'active', NOW(), NOW()),
            (2, 'ASA009', 'AS-B789', 'B789', 'B789', 'YSSY', 'NZAA', -33.946111, 151.177222, -37.008056, 174.791667, -36.500000, 167.000000, 90, 38000, 489, 'cruise', 'active', NOW(), NOW()),
            (3, 'ASA005', 'AS-A320', 'A320', 'A320', 'YSSY', 'YPPH', -33.946111, 151.177222, -31.940278, 115.966944, -34.500000, 144.000000, 270, 35000, 420, 'cruise', 'active', NOW(), NOW()),
            (4, 'ASA013', 'AS-B772', 'B772', 'B772', 'YSSY', 'RJTT', -33.946111, 151.177222, 35.764722, 140.386389, -28.000000, 153.000000, 340, 28000, 350, 'climb', 'active', NOW(), NOW())");

        // ---- SEED: PIREPs ----
        runSQL($pdo, "INSERT IGNORE INTO `pireps` (`id`, `user_id`, `flight_number`, `departure`, `arrival`, `aircraft_registration`, `aircraft_icao`, `flight_time`, `landing_rate`, `score`, `route`, `status`, `submitted_at`) VALUES
            (1, 2, 'ASA001', 'YSSY', 'YMML', 'AS-B738', 'B738', 1.50, -175, 100, 'SYD/MEL', 'approved', NOW()),
            (2, 2, 'ASA003', 'YSSY', 'YBBN', 'AS-A320', 'A320', 1.75, -350, 80, 'SYD/BNE', 'approved', NOW()),
            (3, 2, 'ASA005', 'YSSY', 'YPPH', 'AS-B789', 'B789', 4.50, -600, 60, 'SYD/PER', 'pending', NOW())");

        // ---- SEED: News ----
        runSQL($pdo, "INSERT IGNORE INTO `news` (`id`, `title`, `slug`, `excerpt`, `content`, `author_id`, `published_at`) VALUES
            (1, 'Welcome to Atlantic Star Airways', 'welcome-atlantic-star', 'We are thrilled to announce the launch of Atlantic Star Airways!', 'Welcome aboard! We are a premium Next-Gen Virtual Airline.', 1, NOW()),
            (2, 'New Routes Announced', 'new-routes-asia', 'Expanding our network with new routes to Asia.', 'New routes to Singapore and Tokyo now available!', 1, NOW()),
            (3, 'Pilot Achievement System Launch', 'achievement-system', 'Earn achievements and unlock rewards as you fly!', 'Achievement and Tour system is now live!', 1, NOW())");

        echo '<li class="success">✅ All tables created and seeded successfully!</li>';
        echo '<li class="success">✅ Admin: <strong>' . htmlspecialchars($adminEmail) . '</strong> / <strong>' . htmlspecialchars($adminPassword) . '</strong></li>';
        echo '<li class="success">✅ Pilot: <strong>' . htmlspecialchars($pilotEmail) . '</strong> / <strong>' . htmlspecialchars($pilotPassword) . '</strong></li>';

        if ($errors) {
            echo '<li class="error">⚠️ ' . count($errors) . ' warnings (non-critical):</li>';
            foreach ($errors as $e) {
                echo '<li class="error" style="font-size:.8rem;padding-left:1rem;">' . htmlspecialchars($e) . '</li>';
            }
        }
        ?>
        </ul>
    </div>

    <div class="card">
        <h2>Next Steps</h2>
        <ul>
            <li>✅ <strong>Delete this file</strong> (<code>install.php</code>) from your server</li>
            <li>📝 Update <code>.env</code> with your database credentials</li>
            <li>🔑 Run <code>php artisan key:generate</code> if you have SSH access</li>
            <li>🔗 Visit <code>/login</code> and sign in with the admin credentials above</li>
            <li>⚙️ Go to <strong>Admin → Settings</strong> to configure your airline name, callsign, etc.</li>
        </ul>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
