-- ================================================================
-- KandaNews API v1 — Migration 005: SDUI Foundation
-- Edition categories, polls/voting, events, home banners
-- Run: paste into phpMyAdmin SQL tab on the kandan_api database
-- ================================================================

-- ────────────────────────────────────────
-- 1. Edition Categories  (server-controlled, replaces Flutter hardcode)
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS edition_categories (
    id           SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug         VARCHAR(50)  NOT NULL UNIQUE,
    label        VARCHAR(100) NOT NULL,
    description  VARCHAR(255) DEFAULT NULL,
    icon_name    VARCHAR(50)  NOT NULL DEFAULT 'newspaper',  -- Flutter icon key
    color_hex    CHAR(7)      NOT NULL DEFAULT '#F05A1A',
    sort_order   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    edition_type ENUM('special','rate_card') NOT NULL DEFAULT 'special',
    country      CHAR(2)      DEFAULT NULL,   -- NULL = visible in all countries
    is_active    TINYINT(1)   NOT NULL DEFAULT 1,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active_country (is_active, country)
) ENGINE=InnoDB;

-- ────────────────────────────────────────
-- 2. Polls / Voting Campaigns
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS polls (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question        VARCHAR(500) NOT NULL,
    description     TEXT         DEFAULT NULL,
    cover_image_url VARCHAR(500) DEFAULT NULL,  -- banner image for the poll card
    country         CHAR(2)      NOT NULL DEFAULT 'ug',
    status          ENUM('draft','active','closed') NOT NULL DEFAULT 'draft',
    starts_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ends_at         DATETIME     DEFAULT NULL,   -- NULL = no expiry
    sort_order      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_country_status (country, status),
    INDEX idx_sort            (sort_order)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS poll_options (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poll_id     BIGINT UNSIGNED NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    image_url   VARCHAR(500) DEFAULT NULL,  -- profile pic / candidate photo
    sort_order  TINYINT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
    INDEX idx_poll_order (poll_id, sort_order)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS poll_votes (
    id        BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poll_id   BIGINT UNSIGNED NOT NULL,
    option_id BIGINT UNSIGNED NOT NULL,
    user_id   BIGINT UNSIGNED NOT NULL,
    voted_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_user_poll   (user_id, poll_id),      -- one vote per user per poll
    FOREIGN KEY (poll_id)   REFERENCES polls(id)       ON DELETE CASCADE,
    FOREIGN KEY (option_id) REFERENCES poll_options(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)   REFERENCES users(id)       ON DELETE CASCADE,
    INDEX idx_poll_tally (poll_id, option_id)
) ENGINE=InnoDB;

-- ────────────────────────────────────────
-- 3. Events
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS events (
    id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title            VARCHAR(255) NOT NULL,
    description      TEXT         DEFAULT NULL,
    event_date       DATETIME     NOT NULL,
    end_date         DATETIME     DEFAULT NULL,
    location         VARCHAR(255) DEFAULT NULL,
    is_online        TINYINT(1)   NOT NULL DEFAULT 0,
    registration_url VARCHAR(500) DEFAULT NULL,
    cover_image_url  VARCHAR(500) DEFAULT NULL,   -- event banner
    country          CHAR(2)      NOT NULL DEFAULT 'ug',
    category         ENUM('conference','webinar','workshop','networking','launch','other') DEFAULT 'other',
    status           ENUM('draft','published','cancelled') NOT NULL DEFAULT 'draft',
    is_free          TINYINT(1)   NOT NULL DEFAULT 1,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_country_status_date (country, status, event_date)
) ENGINE=InnoDB;

-- ────────────────────────────────────────
-- 4. Home Banners  (promotional strip / announcement)
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS home_banners (
    id           SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(255) NOT NULL,
    subtitle     VARCHAR(255) DEFAULT NULL,
    action_url   VARCHAR(500) DEFAULT NULL,   -- deep link or external URL
    action_label VARCHAR(100) DEFAULT NULL,
    bg_color_hex CHAR(7)      NOT NULL DEFAULT '#F05A1A',
    icon_name    VARCHAR(50)  DEFAULT NULL,
    country      CHAR(2)      DEFAULT NULL,
    is_active    TINYINT(1)   NOT NULL DEFAULT 1,
    starts_at    DATETIME     DEFAULT NULL,
    ends_at      DATETIME     DEFAULT NULL,
    sort_order   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active_country (is_active, country)
) ENGINE=InnoDB;

-- ────────────────────────────────────────
-- 5. Seed: Edition Categories
--    Migrates the hardcoded Flutter list to the DB.
--    INSERT IGNORE is safe to re-run.
-- ────────────────────────────────────────
INSERT IGNORE INTO edition_categories
    (slug, label, description, icon_name, color_hex, sort_order, edition_type)
VALUES
    ('university',       'University',       'Campus news & academic editions',        'school',        '#3B82F6', 1, 'special'),
    ('corporate',        'Corporate',        'Business & enterprise editions',         'business',      '#1E2B42', 2, 'special'),
    ('entrepreneurship', 'Entrepreneurship', 'Startup & innovation stories',           'rocket_launch', '#F05A1A', 3, 'special'),
    ('campaigns',        'Campaigns',        'Political & advocacy coverage',          'campaign',      '#EF4444', 4, 'special'),
    ('jobs_careers',     'Jobs & Careers',   'Opportunities & career guidance',        'work',          '#10B981', 5, 'special'),
    ('podcasts',         'Podcasts',         'Audio shows & transcripts',              'podcasts',      '#8B5CF6', 6, 'special'),
    ('episodes',         'Episodes',         'Series & episodic content',              'play_circle',   '#F59E0B', 7, 'special'),
    ('rate_card',        'Rate Card',        'Advertising rates & media kit',          'price_change',  '#6D28D9', 8, 'rate_card');
