-- Add year column to club_equipment table for year-wise tracking
-- This migration adds an explicit year column instead of extracting from created_at

ALTER TABLE club_equipment 
ADD COLUMN year INT NOT NULL DEFAULT 2024 COMMENT 'Year for equipment tracking' AFTER quantity;

-- Add index on year column for better query performance
ALTER TABLE club_equipment 
ADD INDEX idx_equipment_year (year);

-- Update existing records to extract year from created_at
UPDATE club_equipment 
SET year = YEAR(created_at) 
WHERE year = 2024;
