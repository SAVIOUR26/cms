-- ================================================================
-- KandaNews API v1 — Database Schema
-- Run once: mysql -u root -p kandan_api < migration.sql
-- ================================================================

CREATE DATABASE IF NOT EXISTS kandan_api
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE kandan_api;

-- ────────────────────────────────────────
-- 1. Users (phone = primary identity)
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone       VARCHAR(20)  NOT NULL UNIQUE,
    first_name  VARCHAR(50)  DEFAULT NULL,
    surname     VARCHAR(50)  DEFAULT NULL,
    full_name   VARCHAR(100) DEFAULT NULL,
    email       VARCHAR(255) DEFAULT NULL,
    age         TINYINT UNSIGNED DEFAULT NULL,
    role        ENUM('student', 'professional', 'entrepreneur') DEFAULT NULL,
    role_detail VARCHAR(200) DEFAULT NULL,
    country     CHAR(2)      NOT NULL DEFAULT 'ug',
    avatar_url  VARCHAR(500) DEFAULT NULL,
    status      ENUM('active', 'suspended', 'deleted') NOT NULL DEFAULT 'active',
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_country (country),
    INDEX idx_status  (status)
) ENGINE=InnoDB;


-- ────────────────────────────────────────
-- 2. OTP Codes (phone verification)
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS otp_codes (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone       VARCHAR(20)  NOT NULL,
    code_hash   VARCHAR(255) NOT NULL,
    used        TINYINT(1)   NOT NULL DEFAULT 0,
    expires_at  DATETIME     NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_phone_used  (phone, used),
    INDEX idx_expires     (expires_at)
) ENGINE=InnoDB;


-- ────────────────────────────────────────
-- 3. Editions (daily newspaper issues)
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS editions (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(255) NOT NULL,
    slug          VARCHAR(255) NOT NULL,
    country       CHAR(2)      NOT NULL DEFAULT 'ug',
    edition_date  DATE         NOT NULL,
    edition_type  ENUM('daily', 'special', 'rate_card') NOT NULL DEFAULT 'daily',
    cover_image   VARCHAR(500) DEFAULT NULL,
    html_url      VARCHAR(500) DEFAULT NULL,
    zip_url       VARCHAR(500) DEFAULT NULL,
    page_count    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_free       TINYINT(1)   NOT NULL DEFAULT 0,
    theme         VARCHAR(100) DEFAULT NULL,
    description   TEXT         DEFAULT NULL,
    status        ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_slug (slug),
    INDEX idx_country_status_date (country, status, edition_date DESC),
    INDEX idx_type (edition_type)
) ENGINE=InnoDB;


-- ────────────────────────────────────────
-- 4. Edition Pages (individual pages/spreads)
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS edition_pages (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    edition_id     BIGINT UNSIGNED NOT NULL,
    page_number    SMALLINT UNSIGNED NOT NULL,
    title          VARCHAR(255) DEFAULT NULL,
    content_url    VARCHAR(500) NOT NULL,
    thumbnail_url  VARCHAR(500) DEFAULT NULL,

    CONSTRAINT fk_pages_edition FOREIGN KEY (edition_id) REFERENCES editions(id) ON DELETE CASCADE,
    UNIQUE KEY uk_edition_page (edition_id, page_number)
) ENGINE=InnoDB;


-- ────────────────────────────────────────
-- 5. Subscriptions
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS subscriptions (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          BIGINT UNSIGNED NOT NULL,
    plan             ENUM('daily', 'weekly', 'monthly') NOT NULL,
    status           ENUM('pending', 'active', 'expired', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_provider VARCHAR(30)  DEFAULT NULL,
    payment_ref      VARCHAR(100) DEFAULT NULL,
    payment_tx_id    VARCHAR(100) DEFAULT NULL,
    amount           DECIMAL(12,2) NOT NULL DEFAULT 0,
    currency         CHAR(3)      NOT NULL DEFAULT 'UGX',
    starts_at        DATETIME     NOT NULL,
    expires_at       DATETIME     NOT NULL,
    activated_at     DATETIME     DEFAULT NULL,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_sub_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status   (user_id, status),
    INDEX idx_payment_ref   (payment_ref),
    INDEX idx_expires       (expires_at)
) ENGINE=InnoDB;


-- ────────────────────────────────────────
-- 6. Payment Log (audit trail)
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS payment_log (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subscription_id  BIGINT UNSIGNED DEFAULT NULL,
    user_id          BIGINT UNSIGNED DEFAULT NULL,
    payment_ref      VARCHAR(100) DEFAULT NULL,
    payment_tx_id    VARCHAR(100) DEFAULT NULL,
    event            VARCHAR(50)  NOT NULL,
    raw_payload      JSON         DEFAULT NULL,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_sub    (subscription_id),
    INDEX idx_user   (user_id),
    INDEX idx_event  (event)
) ENGINE=InnoDB;


-- ────────────────────────────────────────
-- 7. Quotes of the Day
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS quotes (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote     TEXT         NOT NULL,
    author    VARCHAR(200) DEFAULT NULL,
    active    TINYINT(1)   NOT NULL DEFAULT 1,
    created_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed some starter quotes
INSERT INTO quotes (quote, author) VALUES
('The future belongs to those who believe in the beauty of their dreams.', 'Eleanor Roosevelt'),
('Information is the currency of democracy.', 'Thomas Jefferson'),
('A people without the knowledge of their past history, origin and culture is like a tree without roots.', 'Marcus Garvey'),
('Africa is not just a continent; it is an idea, a concept.', 'Wole Soyinka'),
('The youth of today are the leaders of tomorrow.', 'Nelson Mandela'),
('Education is the most powerful weapon which you can use to change the world.', 'Nelson Mandela'),
('If you want to go fast, go alone. If you want to go far, go together.', 'African Proverb'),
('Knowledge is power. Information is liberating.', 'Kofi Annan'),
('Until the lion learns how to write, every story will glorify the hunter.', 'African Proverb'),
('I am because we are.', 'Ubuntu Philosophy');


-- ────────────────────────────────────────
-- 8. CMS Admin Users (for upload portal)
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS cms_admins (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    full_name   VARCHAR(100) DEFAULT NULL,
    role        ENUM('admin', 'editor') NOT NULL DEFAULT 'editor',
    status      ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    last_login  DATETIME     DEFAULT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin (change password immediately after first login)
INSERT INTO cms_admins (username, password, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'KandaNews Admin', 'admin');
-- Default password: password (change it!)


-- ────────────────────────────────────────
-- Cleanup: auto-expire old OTPs (optional event)
-- ────────────────────────────────────────
-- Run as a cron or MySQL event:
-- DELETE FROM otp_codes WHERE expires_at < NOW() - INTERVAL 1 DAY;
