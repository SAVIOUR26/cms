-- ================================================================
-- KandaNews API — Migration 003: Ads Platform
-- Portal: https://ads.kandanews.africa
-- Run: mysql -u kandan_api -p kandan_api < migration_003_ads.sql
-- ================================================================

USE kandan_api;

-- ────────────────────────────────────────
-- 1. Advertisers
--    Businesses that register on ads.kandanews.africa
--    Completely separate from app users (users table)
--    and CMS staff (cms_admins table)
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ads_advertisers (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_name  VARCHAR(200)  NOT NULL,
    contact_name  VARCHAR(100)  NOT NULL,
    email         VARCHAR(255)  NOT NULL UNIQUE,
    phone         VARCHAR(30)   NOT NULL,
    country       VARCHAR(50)   NOT NULL DEFAULT 'Uganda',
    password      VARCHAR(255)  NOT NULL,
    status        ENUM('active', 'suspended', 'deleted') NOT NULL DEFAULT 'active',
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME      DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_email  (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ────────────────────────────────────────
-- 2. Ad Bookings
--    One row per campaign booking.
--    format_key matches AD_FORMATS keys in ads/shared/config.php:
--      full_page | half_page | video_60 | video_30 |
--      audio_60  | audio_30  | gif_insert | cart_ad |
--      market_listing | sponsored_content
--    Discount logic:
--      days >= 30 → discount_pct = 20
--      days >=  7 → discount_pct = 10
--      else       → discount_pct =  0
--    total_price = subtotal - ROUND(subtotal * discount_pct / 100)
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ads_bookings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    advertiser_id   BIGINT UNSIGNED NOT NULL,

    -- Ad format
    format_key      VARCHAR(50)   NOT NULL,
    format_label    VARCHAR(100)  NOT NULL,

    -- Campaign schedule
    start_date      DATE          NOT NULL,
    end_date        DATE          NOT NULL,
    days            SMALLINT UNSIGNED NOT NULL DEFAULT 1,

    -- Pricing (all amounts in UGX)
    unit_price      INT UNSIGNED  NOT NULL,   -- price per day
    subtotal        INT UNSIGNED  NOT NULL,   -- unit_price × days
    discount_pct    TINYINT UNSIGNED NOT NULL DEFAULT 0,  -- 0 / 10 / 20
    total_price     INT UNSIGNED  NOT NULL,   -- subtotal after discount

    -- Payment
    payment_status  ENUM('pending', 'paid', 'failed') NOT NULL DEFAULT 'pending',
    flw_ref         VARCHAR(100)  DEFAULT NULL,   -- Flutterwave tx_ref  (ADS-{id}-{ts})
    flw_tx_id       VARCHAR(100)  DEFAULT NULL,   -- Flutterwave transaction_id

    -- Campaign lifecycle
    status          ENUM('pending', 'confirmed', 'active', 'completed', 'cancelled')
                    NOT NULL DEFAULT 'pending',

    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_booking_advertiser
        FOREIGN KEY (advertiser_id) REFERENCES ads_advertisers(id) ON DELETE CASCADE,

    INDEX idx_advertiser        (advertiser_id),
    INDEX idx_payment_status    (payment_status),
    INDEX idx_status            (status),
    INDEX idx_start_date        (start_date),
    INDEX idx_flw_ref           (flw_ref),
    INDEX idx_created           (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ────────────────────────────────────────
-- 3. Ads Payment Log
--    Audit trail for every payment event:
--    init → webhook → verify → success / failed / cancelled
--    Mirrors the existing payment_log table pattern.
-- ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ads_payment_log (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id      BIGINT UNSIGNED DEFAULT NULL,   -- NULL if webhook arrives before booking lookup
    advertiser_id   BIGINT UNSIGNED DEFAULT NULL,
    flw_ref         VARCHAR(100)  DEFAULT NULL,
    flw_tx_id       VARCHAR(100)  DEFAULT NULL,
    event           VARCHAR(50)   NOT NULL,         -- init | webhook | verify | success | failed | cancelled
    amount          DECIMAL(12,2) DEFAULT NULL,
    currency        CHAR(3)       NOT NULL DEFAULT 'UGX',
    raw_payload     JSON          DEFAULT NULL,
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_booking     (booking_id),
    INDEX idx_advertiser  (advertiser_id),
    INDEX idx_flw_ref     (flw_ref),
    INDEX idx_event       (event),
    INDEX idx_created     (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ────────────────────────────────────────
-- Useful views for the dashboard
-- ────────────────────────────────────────

-- Revenue summary per advertiser
CREATE OR REPLACE VIEW ads_revenue_by_advertiser AS
SELECT
    a.id              AS advertiser_id,
    a.company_name,
    a.email,
    a.country,
    COUNT(b.id)                                       AS total_bookings,
    SUM(b.total_price)                                AS total_billed_ugx,
    SUM(CASE WHEN b.payment_status = 'paid'
             THEN b.total_price ELSE 0 END)           AS total_paid_ugx,
    SUM(CASE WHEN b.payment_status = 'pending'
             THEN b.total_price ELSE 0 END)           AS total_pending_ugx,
    MAX(b.created_at)                                 AS last_booking_at
FROM ads_advertisers a
LEFT JOIN ads_bookings b ON b.advertiser_id = a.id
GROUP BY a.id;

-- Daily revenue (for charts)
CREATE OR REPLACE VIEW ads_daily_revenue AS
SELECT
    DATE(created_at)      AS day,
    COUNT(*)              AS bookings,
    SUM(total_price)      AS billed_ugx,
    SUM(CASE WHEN payment_status = 'paid' THEN total_price ELSE 0 END) AS paid_ugx
FROM ads_bookings
GROUP BY DATE(created_at)
ORDER BY day DESC;

-- Active campaigns today
CREATE OR REPLACE VIEW ads_active_today AS
SELECT
    b.id,
    a.company_name,
    a.contact_name,
    a.phone,
    b.format_label,
    b.start_date,
    b.end_date,
    b.days,
    b.total_price,
    b.payment_status,
    b.status
FROM ads_bookings b
JOIN ads_advertisers a ON a.id = b.advertiser_id
WHERE b.status IN ('confirmed', 'active')
  AND b.start_date <= CURDATE()
  AND b.end_date   >= CURDATE()
ORDER BY b.start_date ASC;

-- Format performance summary
CREATE OR REPLACE VIEW ads_format_performance AS
SELECT
    format_key,
    format_label,
    COUNT(*)                                                AS total_bookings,
    SUM(days)                                               AS total_days_booked,
    SUM(CASE WHEN payment_status = 'paid'
             THEN total_price ELSE 0 END)                  AS revenue_ugx,
    ROUND(AVG(CASE WHEN payment_status = 'paid'
                   THEN total_price END))                  AS avg_booking_value_ugx
FROM ads_bookings
GROUP BY format_key, format_label
ORDER BY revenue_ugx DESC;
