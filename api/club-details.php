<?php

/**
 * Club Details API
 * Returns full details of a single club
 */

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendJSONResponse(false, null, 'Invalid request method', 405);
    }

    $clubId = $_GET['id'] ?? null;

    if (!$clubId) {
        sendJSONResponse(false, null, 'Club ID is required', 400);
    }

    $pdo = getDBConnection();

    // Get club details with location info
    $sql = "SELECT 
                c.*,
                gn.name as gs_division_name,
                dv.name as division_name,
                d.name as district_name
            FROM clubs c
            LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
            LEFT JOIN divisions dv ON gn.division_id = dv.id
            LEFT JOIN districts d ON dv.district_id = d.id
            WHERE c.id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $clubId]);
    $club = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$club) {
        sendJSONResponse(false, null, 'Club not found', 404);
    }

    // Get equipment
    $equipmentSql = "SELECT 
                        ce.equipment_type_id AS id,
                        et.name,
                        ce.quantity
                     FROM club_equipment ce
                     JOIN equipment_types et ON ce.equipment_type_id = et.id
                     WHERE ce.club_id = :club_id
                     ORDER BY et.name";

    $equipmentStmt = $pdo->prepare($equipmentSql);
    $equipmentStmt->execute(['club_id' => $clubId]);
    $equipment = $equipmentStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get reorganization data
    $reorgSql = "SELECT reorg_date FROM club_reorganizations WHERE club_id = :club_id ORDER BY reorg_date DESC";
    $reorgStmt = $pdo->prepare($reorgSql);
    $reorgStmt->execute(['club_id' => $clubId]);
    $reorgs = $reorgStmt->fetchAll(PDO::FETCH_ASSOC);

    $lastReorgDate = !empty($reorgs) ? $reorgs[0]['reorg_date'] : null;

    // Calculate due date with special rule: if reorg after June, valid until Jan 1 next year + 1
    if ($lastReorgDate) {
        $reorgDateTime = new DateTime($lastReorgDate);
        $reorgMonth = (int)$reorgDateTime->format('n');
        $reorgYear = (int)$reorgDateTime->format('Y');

        if ($reorgMonth > 6) {
            // After June: valid until Jan 1 of year after next
            $reorgDueDate = ($reorgYear + 2) . '-01-01';
        } else {
            // Before or in June: valid until Jan 1 of next year
            $reorgDueDate = ($reorgYear + 1) . '-01-01';
        }
    } else {
        $reorgDueDate = null;
    }

    $status = (!$lastReorgDate || $reorgDueDate <= date('Y-m-d')) ? 'expired' : 'active';

    $club['equipment'] = $equipment;
    $club['reorganizations'] = $reorgs;
    $club['last_reorg_date'] = $lastReorgDate;
    $club['reorg_due_date'] = $reorgDueDate;
    $club['reorg_status'] = $status;

    sendJSONResponse(true, $club, 'Club details loaded successfully');
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}
