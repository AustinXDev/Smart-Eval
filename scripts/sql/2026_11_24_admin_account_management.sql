-- Account Management (super admin only).
--
-- 1. status: 'active' or 'inactive'. Inactive administrators cannot sign in,
--    and any open session is rejected by the API layer (ApiSecurity::admin).
--    Existing accounts stay active.
-- 2. updated_at: last change to the account row.
-- 3. access_pin_hash / pin_failed_attempts / pin_locked_until: the 6-digit PIN
--    each super admin sets to open Account Management (hashed with
--    password_hash; 5 wrong tries lock the page for 15 minutes).
-- 4. Unique username and email (duplicate entries are also checked in
--    AdminAccountService with a clear message; the indexes are the backstop).
--
-- Before running on a server that already has data, check for duplicates:
--   SELECT username, COUNT(*) FROM admins GROUP BY username HAVING COUNT(*) > 1;
--   SELECT email,    COUNT(*) FROM admins GROUP BY email    HAVING COUNT(*) > 1;
-- Safe to run once; re-running fails on the duplicate column and changes nothing.

ALTER TABLE admins
    ADD COLUMN status ENUM('active', 'inactive') NOT NULL DEFAULT 'active' AFTER role,
    ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at,
    ADD COLUMN access_pin_hash VARCHAR(255) NULL DEFAULT NULL AFTER password_hash,
    ADD COLUMN pin_failed_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER access_pin_hash,
    ADD COLUMN pin_locked_until DATETIME NULL DEFAULT NULL AFTER pin_failed_attempts,
    ADD UNIQUE KEY uniq_admins_username (username),
    ADD UNIQUE KEY uniq_admins_email (email);
