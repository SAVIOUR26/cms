-- ================================================================
-- KandaNews API v1 — Migration 006: Banner Analytics
--
-- 1. Adds impression_count + click_count tracking to home_banners.
-- 2. Seeds the default "New? Start here" banner so the hardcoded
--    Flutter widget can be removed — it is now fully server-driven.
--
-- Run: paste into phpMyAdmin SQL tab on the kandan_api database
-- Safe to re-run (ALTER uses IF NOT EXISTS column check style via
-- portal_ensure_schema; INSERT uses INSERT IGNORE).
-- ================================================================

ALTER TABLE home_banners
    ADD COLUMN impression_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER sort_order,
    ADD COLUMN click_count      INT UNSIGNED NOT NULL DEFAULT 0 AFTER impression_count;

-- Default "New? Start here" banner.
-- country = NULL means visible in all countries.
-- sort_order = 1 puts it first after the quote slide.
INSERT IGNORE INTO home_banners
    (title, subtitle, action_url, action_label, bg_color_hex, icon_name,
     country, is_active, sort_order)
VALUES
    (
        'New here? Start here',
        'Discover why Africa''s sharpest minds read KandaNews',
        '/special-editions',
        'Explore',
        '#1E2B42',
        'play_circle',
        NULL,
        1,
        1
    );
