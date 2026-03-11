<?php

/**
 * Dashboard Statistics API
 * Returns key statistics for the dashboard
 */

header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';

try {
    $pdo = getDBConnection();

    // Total clubs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clubs");
    $totalClubs = $stmt->fetch()['total'];

    // Clubs by district
    $stmt = $pdo->query("
        SELECT d.name as district_name, COUNT(c.id) as count
        FROM districts d
        LEFT JOIN divisions dv ON d.id = dv.district_id
        LEFT JOIN grama_niladhari_divisions gn ON dv.id = gn.division_id
        LEFT JOIN clubs c ON gn.id = c.gn_division_id
        GROUP BY d.id, d.name
        ORDER BY d.name
    ");
    $clubsByDistrict = $stmt->fetchAll();

    // Total equipment count (distinct types in use)
    $stmt = $pdo->query("SELECT COUNT(DISTINCT equipment_type_id) as total FROM club_equipment");
    $totalEquipmentTypes = $stmt->fetch()['total'];

    // Recent registrations (last 30 days)
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM clubs 
        WHERE registration_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $recentRegistrations = $stmt->fetch()['total'];

    // Today's registrations
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM clubs 
        WHERE DATE(registration_date) = CURDATE()
    ");
    $todayRegistrations = $stmt->fetch()['total'];

    // Active vs Expired clubs (based on reorganization due date)
    $stmt = $pdo->query("
        SELECT
            SUM(CASE
                WHEN last_reorg IS NULL THEN 1
                WHEN MONTH(last_reorg) >= 7
                     AND DATE(CONCAT(YEAR(last_reorg) + 2, '-01-01')) <= CURDATE() THEN 1
                WHEN MONTH(last_reorg) < 7
                     AND DATE_ADD(last_reorg, INTERVAL 1 YEAR) <= CURDATE() THEN 1
                ELSE 0
            END) AS expired_clubs,
            SUM(CASE
                WHEN last_reorg IS NOT NULL
                     AND MONTH(last_reorg) >= 7
                     AND DATE(CONCAT(YEAR(last_reorg) + 2, '-01-01')) > CURDATE() THEN 1
                WHEN last_reorg IS NOT NULL
                     AND MONTH(last_reorg) < 7
                     AND DATE_ADD(last_reorg, INTERVAL 1 YEAR) > CURDATE() THEN 1
                ELSE 0
            END) AS active_clubs
        FROM (
            SELECT c.id, MAX(cr.reorg_date) AS last_reorg
            FROM clubs c
            LEFT JOIN club_reorganizations cr ON c.id = cr.club_id
            GROUP BY c.id
        ) sub
    ");
    $statusRow = $stmt->fetch();
    $activeClubs  = (int)($statusRow['active_clubs']  ?? 0);
    $expiredClubs = (int)($statusRow['expired_clubs'] ?? 0);

    sendJSONResponse(true, [
        'total_clubs'           => (int)$totalClubs,
        'clubs_by_district'     => $clubsByDistrict,
        'total_equipment_types' => (int)$totalEquipmentTypes,
        'recent_registrations'  => (int)$recentRegistrations,
        'today_registrations'   => (int)$todayRegistrations,
        'active_clubs'          => $activeClubs,
        'expired_clubs'         => $expiredClubs,
    ], 'Statistics retrieved successfully');
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}
