-- Sports Club Management System - Seed Data
-- Initial reference data for districts and standard equipment types

-- ====================
-- SEED DISTRICTS
-- ====================
INSERT INTO districts (id, name, sinhala_letter) VALUES
(1, 'Galle', 'ග'),
(2, 'Matara', 'ම'),
(3, 'Hambantota', 'හ');

-- ====================
-- SEED STANDARD EQUIPMENT TYPES
-- ====================
INSERT INTO equipment_types (id, name, is_standard) VALUES
(1, 'Volleyball', TRUE),
(2, 'Volleyball Net', TRUE),
(3, 'Netball', TRUE),
(4, 'Football', TRUE),
(5, 'Tennis Ball', TRUE),
(6, 'Cricket Bat', TRUE),
(7, 'Wicket Set', TRUE);

-- ====================
-- NOTES:
-- ====================
-- Divisions and Grama Niladhari Divisions tables start empty
-- These will be populated on-the-fly as users register clubs
-- Custom equipment types (is_standard=FALSE) will be added by users as needed
