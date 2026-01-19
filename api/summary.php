<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';

try {
    $pdo = getDBConnection();

    // Total clubs
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clubs");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Active/Expired clubs (check if last_reorg_date column exists)
    try {
        $stmt = $pdo->query("SELECT 
            SUM(CASE WHEN last_reorg_date >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR) THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN last_reorg_date < DATE_SUB(CURDATE(), INTERVAL 2 YEAR) OR last_reorg_date IS NULL THEN 1 ELSE 0 END) as expired
            FROM clubs");
        $status = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // If last_reorg_date doesn't exist, set all as active
        $status = ['active' => $total, 'expired' => 0];
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
