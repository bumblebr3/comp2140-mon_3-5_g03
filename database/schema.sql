
-- Database schema for the Preston Hall visitor sign-in application.

-- User accounts and their Administrator/Security roles.
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','security') NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Resident directory and contact details.
CREATE TABLE IF NOT EXISTS residents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_number VARCHAR(30) NOT NULL UNIQUE,
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80) NOT NULL,
    room_number VARCHAR(30) NOT NULL,
    contact_number VARCHAR(30) NULL,
    email VARCHAR(190) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_resident_name (first_name, last_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Guest identity and contact information.
CREATE TABLE IF NOT EXISTS guests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_number VARCHAR(40) NOT NULL UNIQUE,
    full_name VARCHAR(160) NOT NULL,
    id_type VARCHAR(40) NOT NULL,
    contact_number VARCHAR(30) NULL,
    email VARCHAR(190) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_guest_name (full_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Each visit links one guest, one resident, and the users who sign them in/out.
-- Reminder columns track scheduled departure notifications and retry attempts.
CREATE TABLE IF NOT EXISTS visits (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    guest_id INT UNSIGNED NOT NULL,
    resident_id INT UNSIGNED NOT NULL,
    sign_in_by INT UNSIGNED NOT NULL,
    sign_out_by INT UNSIGNED NULL,
    sign_in_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expected_departure_at DATETIME NULL,
    sign_out_at DATETIME NULL,
    last_reminder_attempt_at DATETIME NULL,
    reminder_sent_at DATETIME NULL,
    reminder_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    reminder_error TEXT NULL,
    INDEX idx_visit_active (sign_out_at, sign_in_at),
    INDEX idx_visit_expected (expected_departure_at, reminder_sent_at),
    CONSTRAINT fk_visit_guest FOREIGN KEY (guest_id) REFERENCES guests(id),
    CONSTRAINT fk_visit_resident FOREIGN KEY (resident_id) REFERENCES residents(id),
    CONSTRAINT fk_visit_signin_user FOREIGN KEY (sign_in_by) REFERENCES users(id),
    CONSTRAINT fk_visit_signout_user FOREIGN KEY (sign_out_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Append-only activity history for actions performed by signed-in users.
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    username VARCHAR(60) NOT NULL,
    action VARCHAR(80) NOT NULL,
    details TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_date (created_at),
    INDEX idx_audit_user_date (user_id, created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
