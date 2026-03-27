-- Migration script to add a dedicated 'year' column to club_equipment

-- 1. Add the new 'year' column
ALTER TABLE club_equipment
ADD COLUMN `year` INT NOT NULL COMMENT 'Year the equipment was acquired' AFTER `quantity`;

-- 2. Populate 'year' from the existing 'created_at' timestamps
UPDATE club_equipment 
SET `year` = YEAR(created_at);

-- 3. Add an index for year-based reporting lookups
CREATE INDEX idx_equipment_year ON club_equipment (`year`);
