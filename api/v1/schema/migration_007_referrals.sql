-- ================================================================
-- KandaNews API v1 — Migration 007: Referral System
-- Run: mysql -u root -p kandan_api < migration_007_referrals.sql
-- ================================================================

USE kandan_api;

-- ────────────────────────────────────────
-- 1. Referral Codes (one per user, lazy-created)
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS referral_codes (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    BIGINT UNSIGNED NOT NULL,
    code       VARCHAR(12) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_user (user_id),
    UNIQUE KEY uk_code (code),
    CONSTRAINT fk_rc_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ────────────────────────────────────────
-- 2. Referrals (who invited who)
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS referrals (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    referrer_id      BIGINT UNSIGNED NOT NULL,
    referred_user_id BIGINT UNSIGNED NOT NULL,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- A user can only be referred once
    UNIQUE KEY uk_referred (referred_user_id),

    INDEX idx_referrer (referrer_id),

    CONSTRAINT fk_ref_referrer FOREIGN KEY (referrer_id)
        REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ref_referred FOREIGN KEY (referred_user_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
