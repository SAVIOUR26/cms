-- Migration 004: Add date_of_birth to users table
-- Replaces age (int) with the actual birth date so we can:
--   - Compute accurate age at any time
--   - Run birthday-based marketing campaigns from the portal

ALTER TABLE users
    ADD COLUMN date_of_birth DATE NULL DEFAULT NULL
        COMMENT 'User date of birth (YYYY-MM-DD). Age is derived from this field.'
    AFTER age;
