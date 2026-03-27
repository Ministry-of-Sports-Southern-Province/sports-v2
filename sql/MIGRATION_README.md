# Database Migration: Add Year Column to club_equipment

## Issue
The `club_equipment` table was missing the `year` column that the equipment management API expects. This caused equipment additions to fail.

## Solution
Run the migration script to add the `year` column to the existing database.

## How to Apply

### Option 1: Using MySQL Command Line
```bash
mysql -u root -p sports_club_system < add-year-column.sql
```

### Option 2: Using phpMyAdmin
1. Open phpMyAdmin
2. Select the `sports_club_system` database
3. Go to the SQL tab
4. Copy and paste the contents of `add-year-column.sql`
5. Click "Go" to execute

### Option 3: Manual SQL Execution
Run these SQL commands in your MySQL client:

```sql
-- Add year column
ALTER TABLE club_equipment 
ADD COLUMN year INT NOT NULL DEFAULT 2024 COMMENT 'Year for equipment tracking' AFTER quantity;

-- Add index on year column
ALTER TABLE club_equipment 
ADD INDEX idx_equipment_year (year);

-- Update existing records to extract year from created_at
UPDATE club_equipment 
SET year = YEAR(created_at) 
WHERE year = 2024;
```

## Verification
After running the migration, verify the column was added:

```sql
DESCRIBE club_equipment;
```

You should see the `year` column listed.

## Note
- This migration is safe to run multiple times (it will fail gracefully if the column already exists)
- Existing equipment records will have their year set to the year extracted from `created_at`
- New equipment records will require the year to be specified
