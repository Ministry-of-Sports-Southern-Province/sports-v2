<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';

try {
    $pdo = getDBConnection();

    // Total clubs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clubs");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Active/Expired clubs — compute from club_reorganizations (robust for schemas
    // that store reorg dates in a separate table). NULL (no reorg) is considered expired.
    try {
        $sql = "SELECT 
            SUM(CASE WHEN (due_date IS NOT NULL AND due_date > CURDATE()) THEN 1 ELSE 0 END) AS active,
            SUM(CASE WHEN (due_date IS NULL OR due_date <= CURDATE()) THEN 1 ELSE 0 END) AS expired
            FROM (
                SELECT c.id,
                    CASE
                        WHEN MAX(cr.reorg_date) IS NULL THEN NULL
                        WHEN MONTH(MAX(cr.reorg_date)) > 6 THEN STR_TO_DATE(CONCAT(YEAR(MAX(cr.reorg_date)) + 2, '-01-01'), '%Y-%m-%d')
                        ELSE DATE_ADD(MAX(cr.reorg_date), INTERVAL 1 YEAR)
                    END AS due_date
                FROM clubs c
                LEFT JOIN club_reorganizations cr ON c.id = cr.club_id
                GROUP BY c.id
            ) AS t";

        $stmt = $pdo->query($sql);
        $status = $stmt->fetch(PDO::FETCH_ASSOC);
        // ensure integers
        $status['active'] = (int)($status['active'] ?? 0);
        $status['expired'] = (int)($status['expired'] ?? 0);
    } catch (Exception $e) {
        // Fallback: attempt the older clubs.last_reorg_date method, otherwise mark all active
        try {
            $stmt = $pdo->query("SELECT 
                SUM(CASE WHEN last_reorg_date >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR) THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN last_reorg_date < DATE_SUB(CURDATE(), INTERVAL 2 YEAR) OR last_reorg_date IS NULL THEN 1 ELSE 0 END) as expired
                FROM clubs");
            $status = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e2) {
            $status = ['active' => $total, 'expired' => 0];
        }
    }

    // By district
    try {
        $stmt = $pdo->query("SELECT d.name as district, COUNT(c.id) as count 
            FROM districts d 
            LEFT JOIN clubs c ON c.district_id = d.id 
            GROUP BY d.id, d.name 
            ORDER BY d.name");
        $byDistrict = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // If districts table doesn't exist, return empty array
        $byDistrict = [];
    }

    // Total reorganizations (check if table exists)
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM club_reorganizations");
        $totalReorgs = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (Exception $e) {
        $totalReorgs = 0;
    }

    // Registration trend (last 12 months)
    try {
        $stmt = $pdo->query("SELECT 
            DATE_FORMAT(registration_date, '%Y-%m') as month,
            COUNT(*) as count
            FROM clubs
            WHERE registration_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(registration_date, '%Y-%m')
            ORDER BY month");
        $registrationTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $registrationTrend = [];
    }

    sendJSONResponse(true, [
        'total' => (int)$total,
        'active' => (int)$status['active'],
        'expired' => (int)$status['expired'],
        'totalReorgs' => (int)$totalReorgs,
        'byDistrict' => $byDistrict,
        'byStatus' => $status,
        'registrationTrend' => $registrationTrend
    ]);
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}
