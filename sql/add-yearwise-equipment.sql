-- Add year-wise equipment tracking with immutable cross-year separation
-- Safe to run on existing databases.

CREATE TABLE IF NOT EXISTS club_equipment_yearly (
    id INT AUTO_INCREMENT PRIMARY KEY,
    club_id INT NOT NULL,
    equipment_type_id INT NOT NULL,
    reporting_year SMALLINT NOT NULL,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_equipment_yearly_club FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_equipment_yearly_type FOREIGN KEY (equipment_type_id) REFERENCES equipment_types(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_equipment_yearly_quantity CHECK (quantity >= 0),
    UNIQUE KEY unique_equipment_per_club_year (club_id, equipment_type_id, reporting_year),
    INDEX idx_equipment_yearly_year (reporting_year),
    INDEX idx_equipment_yearly_club_year (club_id, reporting_year),
    INDEX idx_equipment_yearly_type_year (equipment_type_id, reporting_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS club_equipment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    club_id INT NOT NULL,
    equipment_type_id INT NOT NULL,
    reporting_year SMALLINT NOT NULL,
    quantity_delta INT NOT NULL,
    action_type ENUM('registration', 'update', 'adjustment') NOT NULL DEFAULT 'update',
    event_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_equipment_txn_club FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_equipment_txn_type FOREIGN KEY (equipment_type_id) REFERENCES equipment_types(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_equipment_txn_year (reporting_year),
    INDEX idx_equipment_txn_club_year (club_id, reporting_year),
    INDEX idx_equipment_txn_type_year (equipment_type_id, reporting_year),
    INDEX idx_equipment_txn_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional one-time bootstrap from legacy table into current year snapshot.
-- Uncomment only if you intentionally want to carry current legacy quantities forward.
-- INSERT INTO club_equipment_yearly (club_id, equipment_type_id, reporting_year, quantity)
-- SELECT ce.club_id, ce.equipment_type_id, YEAR(CURDATE()), ce.quantity
-- FROM club_equipment ce
-- ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), updated_at = CURRENT_TIMESTAMP;
