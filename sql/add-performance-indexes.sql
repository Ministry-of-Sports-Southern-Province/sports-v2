-- Performance Optimization Indexes
-- Add missing indexes for frequently filtered/sorted columns

-- Indexes on clubs table
ALTER TABLE clubs ADD INDEX idx_clubs_registration_date (registration_date);
ALTER TABLE clubs ADD INDEX idx_clubs_name (name);
ALTER TABLE clubs ADD INDEX idx_clubs_chairman_name (chairman_name);

-- Indexes on club_reorganizations table
ALTER TABLE club_reorganizations ADD INDEX idx_reorg_club_date (club_id, reorg_date);
ALTER TABLE club_reorganizations ADD INDEX idx_reorg_date_range (reorg_date);

-- Indexes on districts/divisions for location filters
ALTER TABLE districts ADD INDEX idx_district_name_unique (name);
ALTER TABLE divisions ADD INDEX idx_division_district_name (district_id, name);
ALTER TABLE grama_niladhari_divisions ADD INDEX idx_gn_division_name (division_id, name);

-- Composite index for common club lookup pattern
ALTER TABLE clubs ADD INDEX idx_clubs_gn_reg_date (gn_division_id, registration_date);
ALTER TABLE club_equipment ADD INDEX idx_equipment_club_type (club_id, equipment_type_id);

-- Index for club_reorganizations summary queries
ALTER TABLE club_reorganizations ADD INDEX idx_reorg_club_year (club_id, reorg_date);
