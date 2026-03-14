-- ================================================================
-- KandaNews API v1 — Migration 008: SDUI Card Config for Special Editions
--
-- Adds a `card_config` JSON column to the editions table.
-- This column stores the complete Server-Driven UI configuration that
-- the Flutter app uses to render special edition cards without any
-- hardcoded UI logic. Every visual property of a special edition card
-- (colors, layout, badge, background, CTA) is controlled from the portal.
--
-- Run: paste into phpMyAdmin SQL tab on the kandan_api database
-- Safe to re-run: uses IF NOT EXISTS / column existence checks
-- ================================================================

-- ────────────────────────────────────────────────────────────────
-- 1. Add card_config column to editions
-- ────────────────────────────────────────────────────────────────
-- Stores the full SDUI card configuration as JSON.
-- NULL = use app default rendering (backwards-compatible for daily editions).
-- Only populated for edition_type = 'special' | 'rate_card'.

ALTER TABLE editions
    ADD COLUMN IF NOT EXISTS card_config JSON DEFAULT NULL
        COMMENT 'SDUI card rendering config — see docs/sdui-spec.md';

-- ────────────────────────────────────────────────────────────────
-- 2. Add accent_color to edition_categories
-- ────────────────────────────────────────────────────────────────
-- Allows each category tile in the app to have both a primary color
-- (existing color_hex) and a secondary accent for gradients.

ALTER TABLE edition_categories
    ADD COLUMN IF NOT EXISTS accent_color_hex CHAR(7) DEFAULT NULL
        COMMENT 'Secondary accent for gradient; falls back to color_hex if NULL',
    ADD COLUMN IF NOT EXISTS gradient_angle TINYINT UNSIGNED DEFAULT 135
        COMMENT 'CSS gradient angle in degrees (default 135)';

-- ────────────────────────────────────────────────────────────────
-- 3. Update existing category seeds with accent colors
-- ────────────────────────────────────────────────────────────────
UPDATE edition_categories SET accent_color_hex = '#60A5FA', gradient_angle = 135 WHERE slug = 'university';
UPDATE edition_categories SET accent_color_hex = '#374151', gradient_angle = 160 WHERE slug = 'corporate';
UPDATE edition_categories SET accent_color_hex = '#FF7A3D', gradient_angle = 135 WHERE slug = 'entrepreneurship';
UPDATE edition_categories SET accent_color_hex = '#F87171', gradient_angle = 145 WHERE slug = 'campaigns';
UPDATE edition_categories SET accent_color_hex = '#34D399', gradient_angle = 135 WHERE slug = 'jobs_careers';
UPDATE edition_categories SET accent_color_hex = '#A78BFA', gradient_angle = 150 WHERE slug = 'podcasts';
UPDATE edition_categories SET accent_color_hex = '#FCD34D', gradient_angle = 135 WHERE slug = 'episodes';
UPDATE edition_categories SET accent_color_hex = '#7C3AED', gradient_angle = 140 WHERE slug = 'rate_card';

-- ────────────────────────────────────────────────────────────────
-- 4. Reference: card_config JSON schema (documentation only)
-- ────────────────────────────────────────────────────────────────
-- The JSON stored in card_config follows this structure.
-- See docs/sdui-spec.md for the full authoritative specification.
--
-- {
--   "version": 1,
--   "preset": "university",
--   "card": {
--     "layout": "full_bleed",           -- full_bleed | split | compact | hero
--     "background": {
--       "type": "gradient",             -- gradient | solid | image | image_overlay
--       "colors": ["#1e2b42","#3B82F6"],
--       "angle": 135
--     },
--     "cover_treatment": "overlay"      -- none | overlay | blur_bottom
--   },
--   "badge": {
--     "text": "UNIVERSITY",
--     "bg_color": "#3B82F6",
--     "text_color": "#FFFFFF",
--     "icon": "school"                  -- Flutter icon name
--   },
--   "typography": {
--     "title_color": "#FFFFFF",
--     "subtitle_color": "rgba(255,255,255,0.8)",
--     "accent_color": "#FCD34D"
--   },
--   "cta": {
--     "label": "Read Edition",
--     "bg_color": "#FCD34D",
--     "text_color": "#1e2b42"
--   }
-- }
