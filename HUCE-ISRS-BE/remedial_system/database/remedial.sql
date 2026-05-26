-- =============================================================================
-- HUCE-ISRS Remedial System — Schema reference (MySQL 8+)
-- =============================================================================
-- Nguồn sự thật: Laravel migrations trong database/migrations/
-- Khởi tạo DB: php artisan migrate && php artisan db:seed
--
-- File này dùng để tham khảo / review schema, KHÔNG thay thế migrate.
-- Cột lecturer_emal: typo lịch sử, giữ đồng bộ với DB hiện tại.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- users (Laravel + remedial extensions)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(255) NOT NULL DEFAULT 'sinh_vien',
  `student_code` VARCHAR(255) NULL,
  `department_id` BIGINT UNSIGNED NULL,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `remember_token` VARCHAR(100) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_student_code_index` (`student_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- departments
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `departments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `department_code` VARCHAR(255) NOT NULL,
  `faculty_code` VARCHAR(255) NOT NULL,
  `faculty_name` VARCHAR(255) NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NULL,
  `phone_number` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- remedial_terms
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `remedial_terms` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `year` INT NOT NULL,
  `semester` INT NOT NULL,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME NOT NULL,
  `registration_start` DATETIME NOT NULL,
  `registration_end` DATETIME NOT NULL,
  `remedial_coefficient` INT NULL,
  `price_per_period` INT NULL,
  `price_coefficient` INT NULL,
  `is_current_term` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- subjects
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `subject_code` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `credits` INT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  `department_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `subjects_department_id_foreign`
    FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- students (profile cache; đăng ký FK trỏ users.id)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_code` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(255) NULL,
  `email` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `students_student_code_unique` (`student_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- remedial_registrations
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `remedial_registrations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `remedial_periods` INT NOT NULL,
  `registration_date` DATETIME NOT NULL,
  `lecture_name` VARCHAR(255) NULL,
  `lecturer_phone_number` VARCHAR(255) NULL,
  `lecturer_emal` VARCHAR(255) NULL,
  `student_id` BIGINT UNSIGNED NOT NULL,
  `remedial_term_id` BIGINT UNSIGNED NOT NULL,
  `subject_id` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `remedial_registrations_student_id_foreign`
    FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `remedial_registrations_remedial_term_id_foreign`
    FOREIGN KEY (`remedial_term_id`) REFERENCES `remedial_terms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `remedial_registrations_subject_id_foreign`
    FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- system_configurations
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `system_configurations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(255) NOT NULL,
  `value` VARCHAR(255) NOT NULL,
  `description` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_configurations_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- personal_access_tokens (Sanctum)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` VARCHAR(255) NOT NULL,
  `tokenable_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `abilities` TEXT NULL,
  `last_used_at` TIMESTAMP NULL,
  `expires_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`, `tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- Tài khoản mẫu (sau db:seed — mật khẩu đã hash trong DB thật)
-- =============================================================================
-- | Role      | Đăng nhập                    | Mật khẩu     |
-- |-----------|------------------------------|--------------|
-- | admin     | admin@remedial.edu.vn        | Admin@2024!  |
-- | bo_mon    | bokhoa.cntt@remedial.edu.vn  | BoMon@2024!  |
-- | sinh_vien | student_code (VD: SV001)     | = mã SV      |
-- Sinh viên: auto-provision qua University System (cổng 8001).
