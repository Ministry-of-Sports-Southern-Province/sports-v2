-- ===================================================================
-- MIGRATION SCRIPT: Enable Year-wise Equipment Tracking
-- ===================================================================
-- Purpose: Prepare club_equipment table for year-wise tracking
--
-- Changes:
-- 1. Removes UNIQUE constraint on (club_id, equipment_type_id)
--    to allow multiple entries per equipment type per year
-- 2. Adds index on created_at for efficient year-based filtering
-- 3. All existing equipment retains their created_at timestamps
--
-- Execution: Run this script once after updating schema.sql
-- ===================================================================

-- Step 1: Drop the UNIQUE constraint if it exists
ALTER TABLE `club_equipment` DROP INDEX `unique_equipment_per_club`;

-- Step 2: Add index on created_at if not exists (for year-wise filtering)
ALTER TABLE `club_equipment` ADD INDEX `idx_equipment_created_at` (`created_at`);

-- Verification queries (run these to verify the migration):
-- SELECT * FROM club_equipment LIMIT 5;  -- Should show existing equipment still there
-- SELECT YEAR(created_at), COUNT(*) FROM club_equipment GROUP BY YEAR(created_at);  -- Group by year
-- SHOW KEYS FROM club_equipment;  -- Check indexes

-- ===================================================================
-- Notes:
-- ===================================================================
-- - Year can be extracted using: YEAR(created_at) or DATE_FORMAT(created_at, '%Y')
-- - Example: SELECT club_id, equipment_type_id, YEAR(created_at) as year, quantity
--           FROM club_equipment WHERE club_id = ? ORDER BY created_at DESC;
-- - To manually adjust year for existing equipment:
--   UPDATE club_equipment SET created_at = CONCAT('YYYY-01-01 ', TIME(created_at))
--   WHERE YEAR(created_at) = YYYY AND club_id = ?;
-- ===================================================================
