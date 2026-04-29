-- =============================================================================
-- Alumni Influencers — coursework schema (Step 2)
-- MySQL / MariaDB (InnoDB, utf8mb4)
-- Import after creating a database whose name matches application/config DB_DATABASE / .env
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- users — authentication and account security (normalized away from profile data)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `api_usage_logs`;
DROP TABLE IF EXISTS `api_keys`;
DROP TABLE IF EXISTS `alumni_event_bonus`;
DROP TABLE IF EXISTS `featured_alumni`;
DROP TABLE IF EXISTS `bids`;
DROP TABLE IF EXISTS `employment_history`;
DROP TABLE IF EXISTS `short_courses`;
DROP TABLE IF EXISTS `licences`;
DROP TABLE IF EXISTS `certifications`;
DROP TABLE IF EXISTS `degrees`;
DROP TABLE IF EXISTS `profiles`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `ci_sessions`;

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','alumni','viewer') NOT NULL DEFAULT 'alumni',
  `status` enum('pending_verification','active','suspended','deleted') NOT NULL DEFAULT 'pending_verification',
  `email_verified_at` datetime DEFAULT NULL,
  `email_verify_token_hash` char(64) DEFAULT NULL COMMENT 'SHA-256 hex of verification token',
  `email_verify_token_expires_at` datetime DEFAULT NULL,
  `password_reset_token_hash` char(64) DEFAULT NULL,
  `password_reset_expires_at` datetime DEFAULT NULL,
  `failed_login_count` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`),
  KEY `idx_users_status` (`status`),
  KEY `idx_users_email_verify_token_hash` (`email_verify_token_hash`),
  KEY `idx_users_password_reset_token_hash` (`password_reset_token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- profiles — 1:1 extension of users (public alumni CV data)
-- -----------------------------------------------------------------------------
CREATE TABLE `profiles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `display_name` varchar(150) NOT NULL,
  `headline` varchar(255) DEFAULT NULL,
  `bio` text,
  `photo_path` varchar(512) DEFAULT NULL,
  `linkedin_url` varchar(512) DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_profiles_user_id` (`user_id`),
  CONSTRAINT `fk_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- Repeatable profile sections (all hang off profiles.id)
-- -----------------------------------------------------------------------------
CREATE TABLE `degrees` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `institution` varchar(255) NOT NULL,
  `qualification` varchar(255) NOT NULL,
  `field_of_study` varchar(255) DEFAULT NULL,
  `grade_or_classification` varchar(100) DEFAULT NULL,
  `started_on` date DEFAULT NULL,
  `completed_on` date DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_degrees_profile_sort` (`profile_id`,`sort_order`),
  CONSTRAINT `fk_degrees_profile` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `certifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `issuer` varchar(255) DEFAULT NULL,
  `credential_id` varchar(128) DEFAULT NULL,
  `issued_on` date DEFAULT NULL,
  `expires_on` date DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_certifications_profile_sort` (`profile_id`,`sort_order`),
  CONSTRAINT `fk_certifications_profile` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `licences` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `issuing_body` varchar(255) DEFAULT NULL,
  `licence_number` varchar(128) DEFAULT NULL,
  `jurisdiction` varchar(128) DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_licences_profile_sort` (`profile_id`,`sort_order`),
  CONSTRAINT `fk_licences_profile` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `short_courses` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `completed_on` date DEFAULT NULL,
  `hours` decimal(6,2) DEFAULT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_short_courses_profile_sort` (`profile_id`,`sort_order`),
  CONSTRAINT `fk_short_courses_profile` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `employment_history` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `employer` varchar(255) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `started_on` date DEFAULT NULL,
  `ended_on` date DEFAULT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `description` text,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_employment_profile_sort` (`profile_id`,`sort_order`),
  KEY `idx_employment_current` (`profile_id`,`is_current`),
  CONSTRAINT `fk_employment_profile` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- bids — blind-bidding hooks (one sealed bid per user per cycle)
-- -----------------------------------------------------------------------------
CREATE TABLE `bids` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `cycle_id` int(10) UNSIGNED NOT NULL COMMENT 'Blind auction / campaign round identifier',
  `amount` decimal(12,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'GBP',
  `status` enum('draft','submitted','withdrawn','won','lost','disqualified') NOT NULL DEFAULT 'submitted',
  `submitted_at` datetime DEFAULT NULL,
  `revealed_at` datetime DEFAULT NULL COMMENT 'When blind amount was revealed to admins',
  `admin_notes` varchar(512) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_bids_user_cycle` (`user_id`,`cycle_id`),
  KEY `idx_bids_cycle_status` (`cycle_id`,`status`),
  CONSTRAINT `fk_bids_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- featured_alumni — homepage / spotlight rows, optionally tied to winning bid
-- -----------------------------------------------------------------------------
CREATE TABLE `featured_alumni` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `cycle_id` int(10) UNSIGNED NOT NULL,
  `winning_bid_id` bigint(20) UNSIGNED DEFAULT NULL,
  `featured_from` datetime NOT NULL,
  `featured_until` datetime NOT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_featured_active_until` (`is_active`,`featured_until`),
  KEY `idx_featured_profile` (`profile_id`),
  KEY `idx_featured_cycle` (`cycle_id`),
  CONSTRAINT `fk_featured_profile` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_featured_winning_bid` FOREIGN KEY (`winning_bid_id`) REFERENCES `bids` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- alumni_event_bonus — optional scoring multiplier / recognition tied to events
-- -----------------------------------------------------------------------------
CREATE TABLE `alumni_event_bonus` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `profile_id` bigint(20) UNSIGNED NOT NULL,
  `event_code` varchar(64) NOT NULL,
  `event_title` varchar(255) NOT NULL,
  `bonus_points` int(11) NOT NULL DEFAULT 0,
  `multiplier` decimal(5,2) NOT NULL DEFAULT 1.00,
  `awarded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime DEFAULT NULL,
  `notes` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_bonus_profile` (`profile_id`),
  KEY `idx_event_bonus_code` (`event_code`),
  CONSTRAINT `fk_event_bonus_profile` FOREIGN KEY (`profile_id`) REFERENCES `profiles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- api_keys — store only hashed secrets; prefix for support / UI
-- -----------------------------------------------------------------------------
CREATE TABLE `api_keys` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `key_prefix` varchar(16) NOT NULL,
  `key_hash` char(64) NOT NULL COMMENT 'SHA-256 hex of full API key',
  `scopes` varchar(512) NOT NULL DEFAULT '' COMMENT 'Comma-separated scope labels',
  `is_revoked` tinyint(1) NOT NULL DEFAULT 0,
  `revoked_at` datetime DEFAULT NULL,
  `revoked_reason` varchar(255) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_api_keys_hash` (`key_hash`),
  KEY `idx_api_keys_user_revoked` (`user_id`,`is_revoked`),
  KEY `idx_api_keys_prefix` (`key_prefix`),
  CONSTRAINT `fk_api_keys_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- api_usage_logs — request audit trail (nullable key after revocation cleanup)
-- -----------------------------------------------------------------------------
CREATE TABLE `api_usage_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `api_key_id` bigint(20) UNSIGNED DEFAULT NULL,
  `route` varchar(255) NOT NULL,
  `http_method` varchar(10) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(512) DEFAULT NULL,
  `response_code` smallint(5) UNSIGNED NOT NULL,
  `duration_ms` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_api_logs_key_created` (`api_key_id`,`created_at`),
  KEY `idx_api_logs_created` (`created_at`),
  CONSTRAINT `fk_api_logs_key` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- ci_sessions — CodeIgniter 3 database sessions (matches sess_save_path)
-- -----------------------------------------------------------------------------
CREATE TABLE `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
