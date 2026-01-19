-- Sports Club Management System - Database Schema
-- Properly normalized database structure with 7 tables
-- Character set: utf8mb4 for Sinhala/Tamil support

-- Drop tables in reverse order of dependencies
DROP TABLE IF EXISTS club_reorganizations;
DROP TABLE IF EXISTS club_equipment;
DROP TABLE IF EXISTS equipment_types;
DROP TABLE IF EXISTS clubs;
DROP TABLE IF EXISTS grama_niladhari_divisions;
DROP TABLE IF EXISTS divisions;
DROP TABLE IF EXISTS districts;

-- ====================
-- 1. DISTRICTS TABLE
-- ====================
CREATE TABLE districts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    sinhala_letter VARCHAR(5) NOT NULL COMMENT 'District letter for reg number: ග/ම/හ',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_district_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- 2. DIVISIONS TABLE
-- ====================
CREATE TABLE divisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    district_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (district_id) REFERENCES districts(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_division_per_district (name, district_id),
    INDEX idx_division_name (name),
    INDEX idx_division_district (district_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- 3. GRAMA NILADHARI DIVISIONS TABLE
-- ====================
CREATE TABLE grama_niladhari_divisions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    division_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (division_id) REFERENCES divisions(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_gn_per_division (name, division_id),
    INDEX idx_gn_name (name),
    INDEX idx_gn_division (division_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- 4. CLUBS TABLE (Main Entity)
-- ====================
CREATE TABLE clubs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reg_number VARCHAR(50) NOT NULL UNIQUE COMMENT 'Format: දපස/ක්‍රිඩා/{ග|ම|හ}/{digits}',
    name VARCHAR(255) NOT NULL,
    registration_date DATE NOT NULL,
    date_entry_type ENUM('auto', 'manual') NOT NULL DEFAULT 'auto' COMMENT 'auto=system date, manual=user entered past date',
    
    -- Chairman Information
    chairman_name VARCHAR(255) NOT NULL,
    chairman_address TEXT NOT NULL,
    chairman_phone CHAR(10) NOT NULL COMMENT '10 digit phone number',
    
    -- Secretary Information
    secretary_name VARCHAR(255) NOT NULL,
    secretary_address TEXT NOT NULL,
    secretary_phone CHAR(10) NOT NULL COMMENT '10 digit phone number',
    
    -- Location Reference
    gn_division_id INT NULL COMMENT 'NULL allowed if GN division not yet created',
    
    -- Audit Fields
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (gn_division_id) REFERENCES grama_niladhari_divisions(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_club_reg_number (reg_number),
    INDEX idx_club_name (name),
    INDEX idx_club_reg_date (registration_date),
    INDEX idx_club_gn_division (gn_division_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- 5. EQUIPMENT TYPES TABLE
-- ====================
CREATE TABLE equipment_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    is_standard BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'TRUE for predefined types, FALSE for custom',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_equipment_name (name),
    INDEX idx_equipment_standard (is_standard)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- 6. CLUB EQUIPMENT TABLE (Junction Table)
-- ====================
CREATE TABLE club_equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    club_id INT NOT NULL,
    equipment_type_id INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity >= 1) COMMENT 'Must be at least 1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (equipment_type_id) REFERENCES equipment_types(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_equipment_per_club (club_id, equipment_type_id),
    INDEX idx_equipment_club (club_id),
    INDEX idx_equipment_type (equipment_type_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- 7. CLUB REORGANIZATIONS TABLE
-- ====================
CREATE TABLE club_reorganizations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    club_id INT NOT NULL,
    reorg_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY unique_reorg_per_club_date (club_id, reorg_date),
    INDEX idx_reorg_club (club_id),
    INDEX idx_reorg_date (reorg_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================
-- NOTES:
-- ====================
-- 1. All tables use utf8mb4_unicode_ci collation for proper Sinhala/Tamil support
-- 2. Foreign keys have CASCADE rules for data integrity
-- 3. UNIQUE constraints prevent duplicates (districts, divisions per district, GN per division, equipment per club)
-- 4. Indexes added on search and filter columns for performance
-- 5. Audit columns (created_at, updated_at) track data changes
-- 6. Phone numbers stored as CHAR(10) for consistent format
-- 7. Equipment quantity has CHECK constraint (>= 1) when selected
-- 8. Registration date supports both auto (current) and manual (past) entry
