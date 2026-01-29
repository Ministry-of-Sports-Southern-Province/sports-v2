# Club Reorganization Database Update

## Overview

This update changes the database structure to support multiple reorganization dates for each club by moving from a single `reco_date` column to a separate `club_reorg` table.

## Database Changes

### New Table: `club_reorg`

- **Purpose**: Store multiple reorganization dates for each club
- **Structure**:
  - `reg_id` (varchar): Foreign key referencing club_register
  - `reorg_date` (date): Reorganization date
  - **Primary Key**: Composite key of (reg_id, reorg_date)
  - **Foreign Key**: reg_id references club_register(reg_id) with CASCADE

### Modified Table: `club_register`

- **Removed**: `reco_date` column (no longer needed)

## Updated Files

### PHP Backend Files

1. **fetch_data.php** - Fetches club data with all reorganization dates (comma-separated)
2. **fetch_summary.php** - Updated to count reorganizations from club_reorg table
3. **filter_view.php** - Filters clubs including reorganization date search
4. **get_data.php** - Retrieves club data with array of reorganization dates
5. **update.php** - Handles updating club info and managing multiple reorganization dates
6. **delete_record.php** - Deletes club and related reorganization dates (CASCADE)

### HTML Frontend Files

1. **view.html** - Displays clubs with reorganization dates (comma-separated list)
2. **edit.html** - Allows adding/removing multiple reorganization dates dynamically
3. **Summary.html** - Shows summary statistics using new structure

## Features

### Edit Page (edit.html)

- Dynamic reorganization date management
- Add new reorganization dates with "+ නව ප්‍රතිසංවිධාන දිනයක් එක් කරන්න" button
- Remove individual dates with "Remove" button
- All existing dates loaded automatically when editing

### View Page (view.html)

- Displays all reorganization dates as comma-separated list
- Maintains existing search and filter functionality
- Shows "N/A" when no reorganization dates exist

### Summary Page (Summary.html)

- Counts clubs with reorganizations in specified year
- Works with new club_reorg table structure

## Installation Steps

1. **Backup your database** before making any changes

2. **Create the new table**:

   ```sql
   -- Run the SQL in db/club_reorg_table.sql
   ```

3. **Migrate existing data** (if you have reco_date data):

   ```sql
   INSERT INTO club_reorg (reg_id, reorg_date)
   SELECT reg_id, reco_date
   FROM club_register
   WHERE reco_date IS NOT NULL;
   ```

4. **Remove old column**:

   ```sql
   ALTER TABLE club_register DROP COLUMN reco_date;
   ```

5. **Clear browser cache** to ensure JavaScript updates load properly

## Usage

### Adding Reorganization Dates

1. Navigate to edit page for a club
2. Click "+ නව ප්‍රතිසංවිධාන දිනයක් එක් කරන්න"
3. Select date from date picker
4. Add multiple dates as needed
5. Click Submit to save

### Removing Reorganization Dates

1. In edit page, click "Remove" button next to any date
2. Click Submit to save changes

### Viewing Reorganization Dates

- View page shows all dates as: "2024-01-15, 2023-06-20, 2022-12-10"
- Dates are sorted in descending order (newest first)

## Design Patterns Maintained

- Clean, user-friendly interface
- Consistent Sinhala language labels
- Bootstrap styling maintained
- Responsive design preserved
- Error handling and validation
- Confirmation dialogs for deletions

## Notes

- Foreign key constraint ensures data integrity
- CASCADE delete removes all reorganization dates when club is deleted
- Empty reorganization dates show as "N/A"
- Date format: YYYY-MM-DD
- All dates sorted in descending order
