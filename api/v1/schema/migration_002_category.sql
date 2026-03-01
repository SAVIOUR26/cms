-- ================================================================
-- KandaNews API v1 — Migration: Add category to editions
-- Run: mysql -u kandan_api -p kandan_api < migration_002_category.sql
-- ================================================================

USE kandan_api;

-- Add category column for special editions filtering
ALTER TABLE editions
  ADD COLUMN category VARCHAR(50) DEFAULT NULL AFTER edition_type,
  ADD INDEX idx_category (category);

-- Valid categories: university, corporate, entrepreneurship, campaigns,
--                   jobs_careers, podcasts, episodes
-- NULL category = general (daily editions typically have no category)
