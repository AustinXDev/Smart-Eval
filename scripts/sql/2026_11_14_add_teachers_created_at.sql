-- Adds teachers.created_at for the "Created" column in Manage Teachers.
--
-- Existing teachers keep NULL (shown as "—"): their real creation date was
-- never recorded, so no date is invented for them. Teachers added after this
-- migration get the insert time automatically.
--
-- Two steps on purpose: adding the column with DEFAULT CURRENT_TIMESTAMP
-- directly would stamp every existing row with the migration time.
-- Safe to run once; re-running fails on the duplicate column and changes nothing.

ALTER TABLE teachers
    ADD COLUMN created_at DATETIME NULL DEFAULT NULL;

ALTER TABLE teachers
    MODIFY COLUMN created_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP;
