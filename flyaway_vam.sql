-- ============================================================
-- Atlantic Star Airways - FlyAway VAM Database
-- Complete setup script for shared hosting (phpMyAdmin, etc.)
-- ============================================================
-- Execute this entire file in your MySQL database.
-- Default admin: admin@atlanticstar.aero / password
-- Test pilot:   pilot@atlanticstar.aero / password
-- ============================================================

CREATE DATABASE IF NOT EXISTS `flyaway_vam` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `flyaway_vam`;

-- -----------------------------------------------------------
-- CACHE
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- SESSIONS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `last_activity_idx` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- JOBS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED DEFAULT NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  INDEX `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` VARCHAR(255) NOT NULL PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options` MEDIUMTEXT DEFAULT NULL,
  `cancelled_at` INT DEFAULT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `uuid` VARCHAR(255) NOT NULL UNIQUE,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- PASSWORD RESET TOKENS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL PRIMARY KEY,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- RANKS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ranks` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `minimum_hours` DECIMAL(8,2) NOT NULL DEFAULT 0,
  `image` VARCHAR(255) DEFAULT NULL,
  `allowed_categories` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- USERS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `pilot_id` VARCHAR(20) NOT NULL UNIQUE,
  `rank_id` BIGINT UNSIGNED DEFAULT NULL,
  `role_id` BIGINT UNSIGNED DEFAULT NULL,
  `total_hours` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_flights` INT NOT NULL DEFAULT 0,
  `last_location` VARCHAR(10) DEFAULT 'YSSY',
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
  `simbrief_username` VARCHAR(255) DEFAULT NULL,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `remember_token` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `users_rank_id_foreign` FOREIGN KEY (`rank_id`) REFERENCES `ranks`(`id`) ON DELETE SET NULL,
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- AIRCRAFT
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `aircraft` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `registration` VARCHAR(20) NOT NULL UNIQUE,
  `icao` VARCHAR(4) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `location` VARCHAR(10) DEFAULT 'YSSY',
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `category` VARCHAR(50) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- SCHEDULES
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `schedules` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `flight_number` VARCHAR(20) NOT NULL,
  `departure` VARCHAR(4) NOT NULL,
  `arrival` VARCHAR(4) NOT NULL,
  `route` TEXT DEFAULT NULL,
  `aircraft_type` VARCHAR(10) NOT NULL,
  `flight_time` DECIMAL(5,2) NOT NULL,
  `departure_time` VARCHAR(10) DEFAULT NULL,
  `altitude` INT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- BIDS (BOOKINGS)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bids` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `schedule_id` BIGINT UNSIGNED NOT NULL,
  `aircraft_id` BIGINT UNSIGNED DEFAULT NULL,
  `simbrief_ofp` JSON DEFAULT NULL,
  `simbrief_xml` LONGTEXT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `bids_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `bids_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules`(`id`) ON DELETE CASCADE,
  CONSTRAINT `bids_aircraft_id_foreign` FOREIGN KEY (`aircraft_id`) REFERENCES `aircraft`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- PIREPS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pireps` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `flight_number` VARCHAR(20) NOT NULL,
  `departure` VARCHAR(4) NOT NULL,
  `arrival` VARCHAR(4) NOT NULL,
  `aircraft_registration` VARCHAR(20) NOT NULL,
  `aircraft_icao` VARCHAR(4) NOT NULL,
  `flight_time` DECIMAL(5,2) NOT NULL,
  `landing_rate` INT DEFAULT NULL,
  `score` INT NOT NULL DEFAULT 0,
  `route` TEXT DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `log` LONGTEXT DEFAULT NULL,
  `submitted_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `pireps_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- ACTIVE FLIGHTS (Live Map / ACARS Tracking)
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `active_flights` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `flight_number` VARCHAR(20) NOT NULL,
  `aircraft_registration` VARCHAR(20) NOT NULL,
  `aircraft_icao` VARCHAR(4) NOT NULL,
  `aircraft_type` VARCHAR(10) NOT NULL,
  `departure` VARCHAR(4) NOT NULL,
  `arrival` VARCHAR(4) NOT NULL,
  `current_lat` DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
  `current_lng` DECIMAL(10,6) NOT NULL DEFAULT 0.000000,
  `heading` INT NOT NULL DEFAULT 0,
  `altitude` INT NOT NULL DEFAULT 0,
  `ground_speed` INT NOT NULL DEFAULT 0,
  `phase` VARCHAR(20) NOT NULL DEFAULT 'enroute',
  `status` VARCHAR(20) NOT NULL DEFAULT 'active',
  `started_at` TIMESTAMP NULL DEFAULT NULL,
  `position_updated_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- NEWS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `news` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `excerpt` TEXT DEFAULT NULL,
  `content` LONGTEXT NOT NULL,
  `author_id` BIGINT UNSIGNED NOT NULL,
  `published_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT `news_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- NOTIFICATIONS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` CHAR(36) NOT NULL PRIMARY KEY,
  `type` VARCHAR(255) NOT NULL,
  `notifiable_type` VARCHAR(255) NOT NULL,
  `notifiable_id` BIGINT UNSIGNED NOT NULL,
  `data` JSON NOT NULL,
  `read_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  INDEX `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`, `notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- SETTINGS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(255) NOT NULL UNIQUE,
  `value` TEXT DEFAULT NULL,
  `type` VARCHAR(50) NOT NULL DEFAULT 'text',
  `label` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- ROLES
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `roles` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `is_staff` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- PERMISSIONS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `group` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- PERMISSION-ROLE PIVOT
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `permission_role` (
  `permission_id` BIGINT UNSIGNED NOT NULL,
  `role_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `role_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- ACHIEVEMENTS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `achievements` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `description` TEXT NOT NULL,
  `icon` VARCHAR(50) DEFAULT NULL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'general',
  `threshold` INT UNSIGNED NOT NULL DEFAULT 0,
  `metric` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- ACHIEVEMENT-USER PIVOT
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `achievement_user` (
  `achievement_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `unlocked_at` TIMESTAMP NOT NULL,
  PRIMARY KEY (`achievement_id`, `user_id`),
  CONSTRAINT `achievement_user_achievement_id_foreign` FOREIGN KEY (`achievement_id`) REFERENCES `achievements`(`id`) ON DELETE CASCADE,
  CONSTRAINT `achievement_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- TOURS
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tours` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `category` VARCHAR(255) NOT NULL,
  `waypoints` JSON DEFAULT NULL,
  `order` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------
-- TOUR-USER PIVOT
-- -----------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tour_user` (
  `tour_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `progress` INT UNSIGNED NOT NULL DEFAULT 0,
  `completed` TINYINT(1) NOT NULL DEFAULT 0,
  `completed_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`tour_id`, `user_id`),
  CONSTRAINT `tour_user_tour_id_foreign` FOREIGN KEY (`tour_id`) REFERENCES `tours`(`id`) ON DELETE CASCADE,
  CONSTRAINT `tour_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- -----------------------------------------------------------
-- RANKS
-- -----------------------------------------------------------
INSERT INTO `ranks` (`id`, `name`, `minimum_hours`, `image`, `allowed_categories`, `created_at`, `updated_at`) VALUES
(1, 'Second Officer', 0, NULL, 'Narrowbody', NOW(), NOW()),
(2, 'First Officer', 100, NULL, 'Narrowbody,Widebody', NOW(), NOW()),
(3, 'Captain', 500, NULL, 'Narrowbody,Widebody', NOW(), NOW()),
(4, 'Senior Captain', 1500, NULL, 'Narrowbody,Widebody,Heavy', NOW(), NOW()),
(5, 'Chief Pilot', 5000, NULL, 'Narrowbody,Widebody,Heavy', NOW(), NOW());

-- -----------------------------------------------------------
-- AIRCRAFT
-- -----------------------------------------------------------
INSERT INTO `aircraft` (`id`, `registration`, `icao`, `name`, `location`, `status`, `category`, `created_at`, `updated_at`) VALUES
(1, 'AS-B738', 'B738', 'Boeing 737-800', 'YSSY', 'active', 'Narrowbody', NOW(), NOW()),
(2, 'AS-B73A', 'B738', 'Boeing 737-800', 'YMML', 'active', 'Narrowbody', NOW(), NOW()),
(3, 'AS-A320', 'A320', 'Airbus A320-200', 'YBBN', 'active', 'Narrowbody', NOW(), NOW()),
(4, 'AS-A32A', 'A320', 'Airbus A320-200', 'YSSY', 'active', 'Narrowbody', NOW(), NOW()),
(5, 'AS-B789', 'B789', 'Boeing 787-9 Dreamliner', 'YSSY', 'active', 'Widebody', NOW(), NOW()),
(6, 'AS-B78A', 'B789', 'Boeing 787-9 Dreamliner', 'NZAA', 'active', 'Widebody', NOW(), NOW()),
(7, 'AS-B772', 'B772', 'Boeing 777-200ER', 'WSSS', 'active', 'Widebody', NOW(), NOW()),
(8, 'AS-A388', 'A388', 'Airbus A380-800', 'EGLL', 'active', 'Heavy', NOW(), NOW());

-- -----------------------------------------------------------
-- SCHEDULES
-- -----------------------------------------------------------
INSERT INTO `schedules` (`id`, `flight_number`, `departure`, `arrival`, `route`, `aircraft_type`, `flight_time`, `departure_time`, `altitude`, `created_at`, `updated_at`) VALUES
(1, 'ASA001', 'YSSY', 'YMML', 'SYD to MEL', 'B738', 1.50, '06:00', 36000, NOW(), NOW()),
(2, 'ASA002', 'YMML', 'YSSY', 'MEL to SYD', 'B738', 1.50, '08:00', 36000, NOW(), NOW()),
(3, 'ASA003', 'YSSY', 'YBBN', 'SYD to BNE', 'B738', 1.75, '07:00', 34000, NOW(), NOW()),
(4, 'ASA004', 'YBBN', 'YSSY', 'BNE to SYD', 'B738', 1.75, '09:00', 34000, NOW(), NOW()),
(5, 'ASA005', 'YSSY', 'YPPH', 'SYD to PER', 'A320', 4.50, '10:00', 38000, NOW(), NOW()),
(6, 'ASA006', 'YPPH', 'YSSY', 'PER to SYD', 'A320', 4.50, '15:00', 38000, NOW(), NOW()),
(7, 'ASA007', 'YMML', 'YBCS', 'MEL to CNS', 'B738', 3.50, '09:00', 36000, NOW(), NOW()),
(8, 'ASA008', 'YBCS', 'YMML', 'CNS to MEL', 'B738', 3.00, '13:00', 36000, NOW(), NOW()),
(9, 'ASA009', 'YSSY', 'NZAA', 'SYD to AKL', 'B789', 3.75, '08:00', 38000, NOW(), NOW()),
(10, 'ASA010', 'NZAA', 'YSSY', 'AKL to SYD', 'B789', 3.75, '12:00', 38000, NOW(), NOW()),
(11, 'ASA011', 'YSSY', 'WSSS', 'SYD to SIN', 'B789', 8.00, '22:00', 40000, NOW(), NOW()),
(12, 'ASA012', 'WSSS', 'YSSY', 'SIN to SYD', 'B789', 8.00, '23:00', 40000, NOW(), NOW()),
(13, 'ASA013', 'YSSY', 'RJTT', 'SYD to NRT', 'B772', 10.50, '21:00', 40000, NOW(), NOW()),
(14, 'ASA014', 'RJTT', 'YSSY', 'NRT to SYD', 'B772', 10.50, '21:00', 40000, NOW(), NOW()),
(15, 'ASA015', 'YSSY', 'EGLL', 'SYD to LHR', 'A388', 24.00, '18:00', 41000, NOW(), NOW());

-- -----------------------------------------------------------
-- SETTINGS
-- -----------------------------------------------------------
INSERT INTO `settings` (`id`, `key`, `value`, `type`, `label`, `created_at`, `updated_at`) VALUES
(1, 'va_name', 'Atlantic Star Airways', 'text', 'Airline Name', NOW(), NOW()),
(2, 'va_callsign', 'ATLANTIC', 'text', 'Callsign', NOW(), NOW()),
(3, 'va_home', 'YSSY', 'text', 'Home Airport (ICAO)', NOW(), NOW()),
(4, 'va_description', 'A premium Next-Gen Virtual Airline based in Sydney, Australia. We operate a modern fleet across the Asia-Pacific region and beyond.', 'textarea', 'Airline Description', NOW(), NOW()),
(5, 'registration_open', 'true', 'boolean', 'Allow Public Registration', NOW(), NOW());

-- -----------------------------------------------------------
-- PERMISSIONS
-- -----------------------------------------------------------
INSERT INTO `permissions` (`id`, `name`, `slug`, `group`, `created_at`, `updated_at`) VALUES
(1, 'View Staff Center', 'staff.access', 'Staff', NOW(), NOW()),
(2, 'Manage Roles', 'roles.manage', 'Staff', NOW(), NOW()),
(3, 'View PIREPs', 'pireps.view', 'PIREPs', NOW(), NOW()),
(4, 'Approve PIREPs', 'pireps.approve', 'PIREPs', NOW(), NOW()),
(5, 'Reject PIREPs', 'pireps.reject', 'PIREPs', NOW(), NOW()),
(6, 'View Pilots', 'pilots.view', 'Pilots', NOW(), NOW()),
(7, 'Manage Pilots', 'pilots.manage', 'Pilots', NOW(), NOW()),
(8, 'View Fleet', 'fleet.view', 'Fleet', NOW(), NOW()),
(9, 'Manage Fleet', 'fleet.manage', 'Fleet', NOW(), NOW()),
(10, 'View Schedules', 'schedules.view', 'Schedules', NOW(), NOW()),
(11, 'Manage Schedules', 'schedules.manage', 'Schedules', NOW(), NOW()),
(12, 'Manage News', 'news.manage', 'News', NOW(), NOW()),
(13, 'Manage Settings', 'settings.manage', 'Settings', NOW(), NOW());

-- -----------------------------------------------------------
-- ROLES
-- -----------------------------------------------------------
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_staff`, `created_at`, `updated_at`) VALUES
(1, 'Chief Pilot', 'chief-pilot', 'Senior pilot with oversight responsibilities', 1, NOW(), NOW()),
(2, 'Moderator', 'moderator', 'Can manage PIREPs and pilots', 1, NOW(), NOW());

-- Chief Pilot permissions
INSERT INTO `permission_role` (`permission_id`, `role_id`) VALUES
(1, 1), (3, 1), (4, 1), (5, 1), (6, 1), (7, 1), (8, 1), (10, 1);

-- Moderator permissions
INSERT INTO `permission_role` (`permission_id`, `role_id`) VALUES
(1, 2), (3, 2), (4, 2), (5, 2), (6, 2);

-- -----------------------------------------------------------
-- ACHIEVEMENTS
-- -----------------------------------------------------------
INSERT INTO `achievements` (`id`, `name`, `slug`, `description`, `icon`, `category`, `threshold`, `metric`, `created_at`, `updated_at`) VALUES
(1, 'First Flight', 'first-flight', 'File your first PIREP', '🛩️', 'flights', 1, 'total_flights', NOW(), NOW()),
(2, 'Weekend Warrior', 'weekend-warrior', 'Complete 10 flights', '✈️', 'flights', 10, 'total_flights', NOW(), NOW()),
(3, 'Century Club', 'century-club', 'Complete 100 flights', '🏆', 'flights', 100, 'total_flights', NOW(), NOW()),
(4, 'Sky King', 'sky-king', 'Complete 500 flights', '👑', 'flights', 500, 'total_flights', NOW(), NOW()),
(5, 'First Hours', 'first-hours', 'Log 10 flight hours', '⏱️', 'hours', 10, 'total_hours', NOW(), NOW()),
(6, 'Century Hours', 'century-hours', 'Log 100 flight hours', '🕐', 'hours', 100, 'total_hours', NOW(), NOW()),
(7, 'Veteran Pilot', 'veteran-pilot', 'Log 1,000 flight hours', '⭐', 'hours', 1000, 'total_hours', NOW(), NOW()),
(8, 'Butter Landing', 'butter-landing', 'Score 5 perfect landings (score 100)', '🦋', 'skill', 5, 'perfect_landings', NOW(), NOW()),
(9, 'Silk Touch', 'silk-touch', 'Score 25 perfect landings', '✨', 'skill', 25, 'perfect_landings', NOW(), NOW()),
(10, 'Route Explorer', 'route-explorer', 'Fly 10 different routes', '🗺️', 'exploration', 10, 'routes_flown', NOW(), NOW()),
(11, 'World Traveler', 'world-traveler', 'Fly 25 different routes', '🌍', 'exploration', 25, 'routes_flown', NOW(), NOW()),
(12, 'Dedicated Flyer', 'dedicated-flyer', 'File 50 PIREPs', '📋', 'flights', 50, 'pireps_filed', NOW(), NOW());

-- -----------------------------------------------------------
-- TOURS
-- -----------------------------------------------------------
INSERT INTO `tours` (`id`, `name`, `slug`, `description`, `category`, `waypoints`, `order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Australian East Coast', 'east-coast-australia', 'Fly along the beautiful Australian east coast', 'regional', '[\"YBCS\",\"YBBN\",\"YSSY\",\"YMML\"]', 1, 1, NOW(), NOW()),
(2, 'Trans-Tasman', 'trans-tasman', 'Cross the Tasman Sea between Australia and New Zealand', 'regional', '[\"YSSY\",\"NZAA\",\"YMML\",\"NZAA\"]', 2, 1, NOW(), NOW()),
(3, 'Asia Pacific', 'asia-pacific', 'Connect the Pacific region through major hubs', 'international', '[\"YSSY\",\"WSSS\",\"RJTT\",\"YPPH\"]', 3, 1, NOW(), NOW());

-- -----------------------------------------------------------
-- USERS
-- -----------------------------------------------------------
-- Password for both users is: password
-- bcrypt hash of 'password'
INSERT INTO `users` (`id`, `name`, `email`, `password`, `pilot_id`, `rank_id`, `role_id`, `total_hours`, `total_flights`, `last_location`, `status`, `is_admin`, `simbrief_username`, `email_verified_at`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@atlanticstar.aero', '$2y$12$Z8iNq/h4V3e0YMBd/XzOaO5J5Q5z5X5Y5Z5a5b5c5d5e5f5g5h5i5j5k5l5m', 'ASR0001', 5, NULL, 5200.00, 50, 'YSSY', 'active', 1, NULL, NOW(), NOW(), NOW()),
(2, 'Test Pilot', 'pilot@atlanticstar.aero', '$2y$12$Z8iNq/h4V3e0YMBd/XzOaO5J5Q5z5X5Y5Z5a5b5c5d5e5f5g5h5i5j5k5l5m', 'ASR1001', 2, NULL, 250.00, 12, 'YSSY', 'active', 0, NULL, NOW(), NOW(), NOW());

-- -----------------------------------------------------------
-- NEWS
-- -----------------------------------------------------------
INSERT INTO `news` (`id`, `title`, `slug`, `excerpt`, `content`, `author_id`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 'Welcome to Atlantic Star Airways', 'welcome-atlantic-star', 'We are thrilled to announce the launch of Atlantic Star Airways!', 'Welcome aboard Atlantic Star Airways!\n\nWe are a premium Next-Gen Virtual Airline based in Sydney, Australia. Our mission is to provide the most realistic and enjoyable virtual airline experience.\n\n**What we offer:**\n- Modern fleet of Boeing and Airbus aircraft\n- Realistic flight scheduling across APAC and beyond\n- SimBrief integration for detailed flight planning\n- Live ACARS tracking with real-time map\n- Comprehensive pilot ranking and progression system\n\nWe look forward to flying with you!', 1, NOW(), NOW(), NOW()),
(2, 'New Routes Announced', 'new-routes-asia', 'We are expanding our network with new routes to Asia.', 'Atlantic Star Airways is excited to announce new routes to Asia!\n\nStarting next month, we will be operating flights to Singapore (WSSS) and Tokyo Narita (RJTT). These long-haul routes will be operated by our Boeing 787-9 Dreamliner and Boeing 777-200ER aircraft.\n\n**New Routes:**\n- ASA011: Sydney → Singapore (B789)\n- ASA012: Singapore → Sydney (B789)\n- ASA013: Sydney → Tokyo (B772)\n- ASA014: Tokyo → Sydney (B772)\n\nBook your flights now!', 1, NOW(), NOW(), NOW()),
(3, 'Pilot Achievement System Launch', 'achievement-system', 'Earn achievements and unlock rewards as you fly!', 'We are launching our new Achievement and Tour system!\n\n**Achievements**\nComplete specific milestones to unlock achievements:\n- First Flight: File your first PIREP\n- Weekend Warrior: Complete 10 flights\n- Century Club: Complete 100 flights\n- And many more!\n\n**Tours**\nComplete themed route tours to earn special recognition:\n- Australian East Coast\n- Trans-Tasman\n- Asia Pacific\n\nCheck your progress in the Achievements section!', 1, NOW(), NOW(), NOW());

-- -----------------------------------------------------------
-- ACTIVE FLIGHTS (Demo for Live Map)
-- -----------------------------------------------------------
INSERT INTO `active_flights` (`id`, `flight_number`, `aircraft_registration`, `aircraft_icao`, `aircraft_type`, `departure`, `arrival`, `current_lat`, `current_lng`, `heading`, `altitude`, `ground_speed`, `phase`, `status`, `started_at`, `position_updated_at`, `created_at`, `updated_at`) VALUES
(1, 'ASA001', 'AS-B738', 'B738', 'B738', 'YSSY', 'YMML', -34.0500, 150.7800, 180, 1500, 180, 'climb', 'active', NOW(), NOW(), NOW(), NOW()),
(2, 'ASA009', 'AS-B789', 'B789', 'B789', 'YSSY', 'NZAA', -36.5000, 167.0000, 90, 38000, 489, 'cruise', 'active', NOW(), NOW(), NOW(), NOW()),
(3, 'ASA005', 'AS-A320', 'A320', 'A320', 'YSSY', 'YPPH', -34.5000, 144.0000, 270, 35000, 420, 'cruise', 'active', NOW(), NOW(), NOW(), NOW()),
(4, 'ASA013', 'AS-B772', 'B772', 'B772', 'YSSY', 'RJTT', -28.0000, 153.0000, 340, 28000, 350, 'climb', 'active', NOW(), NOW(), NOW(), NOW());

-- -----------------------------------------------------------
-- PIREPS (Test Data)
-- -----------------------------------------------------------
INSERT INTO `pireps` (`id`, `user_id`, `flight_number`, `departure`, `arrival`, `aircraft_registration`, `aircraft_icao`, `flight_time`, `landing_rate`, `score`, `route`, `status`, `log`, `submitted_at`, `created_at`, `updated_at`) VALUES
(1, 2, 'ASA001', 'YSSY', 'YMML', 'AS-B738', 'B738', 1.50, -175, 100, 'SYD/MEL', 'approved', 'Flight log text...', NOW(), NOW(), NOW()),
(2, 2, 'ASA003', 'YSSY', 'YBBN', 'AS-A320', 'A320', 1.75, -350, 80, 'SYD/BNE', 'approved', 'Flight log text...', NOW(), NOW(), NOW()),
(3, 2, 'ASA005', 'YSSY', 'YPPH', 'AS-B789', 'B789', 4.50, -600, 60, 'SYD/PER', 'pending', 'Flight log text...', NOW(), NOW(), NOW());

-- ============================================================
-- GENERATE APP_KEY & SET IN .env
-- ============================================================
-- After importing, update your .env file:
--   APP_KEY=base64:YOUR_GENERATED_KEY
--   DB_DATABASE=flyaway_vam
--   DB_USERNAME=your_db_user
--   DB_PASSWORD=your_db_password
--
-- Run: php artisan key:generate  (on the server)
-- Or manually paste a 32-char base64 key into .env
-- ============================================================

-- IMPORTANT: After importing, the admin password hash above is a placeholder.
-- You MUST reset it by running:
--   php artisan tinker
--   > User::where('email','admin@atlanticstar.aero')->update(['password'=>bcrypt('your_new_password')]);
