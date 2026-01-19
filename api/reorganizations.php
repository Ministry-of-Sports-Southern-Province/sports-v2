<?php
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getDBConnection();

    if ($method === 'GET') {
        // Get reorganization history for a club
        $clubId = $_GET['club_id'] ?? null;
        if (!$clubId) {
            sendJSONResponse(false, null, 'Club ID required', 400);
        }

        $sql = "SELECT id, reorg_date, created_at 
                FROM club_reorganizations 
                WHERE club_id = :club_id 
                ORDER BY reorg_date DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['club_id' => $clubId]);
        $reorgs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendJSONResponse(true, $reorgs);
    } elseif ($method === 'POST') {
        // Add new reorganization date
        $data = json_decode(file_get_contents('php://input'), true);
        $clubId = $data['club_id'] ?? null;
        $reorgDate = $data['reorg_date'] ?? null;

        if (!$clubId || !$reorgDate) {
            sendJSONResponse(false, null, 'Club ID and reorganization date required', 400);
        }

        // Check if date already exists
        $checkSql = "SELECT id FROM club_reorganizations WHERE club_id = :club_id AND reorg_date = :reorg_date";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute(['club_id' => $clubId, 'reorg_date' => $reorgDate]);
        if ($checkStmt->fetch()) {
            sendJSONResponse(false, null, 'This reorganization date already exists', 400);
        }

        $sql = "INSERT INTO club_reorganizations (club_id, reorg_date) VALUES (:club_id, :reorg_date)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['club_id' => $clubId, 'reorg_date' => $reorgDate]);

        sendJSONResponse(true, ['id' => $pdo->lastInsertId()], 'Reorganization date added successfully');
    } elseif ($method === 'DELETE') {
        // Delete most recent reorganization
        $data = json_decode(file_get_contents('php://input'), true);
        $clubId = $data['club_id'] ?? null;

        if (!$clubId) {
            sendJSONResponse(false, null, 'Club ID required', 400);
        }

        // Get most recent reorg
        $getSql = "SELECT id FROM club_reorganizations WHERE club_id = :club_id ORDER BY reorg_date DESC LIMIT 1";
        $getStmt = $pdo->prepare($getSql);
        $getStmt->execute(['club_id' => $clubId]);
        $reorg = $getStmt->fetch(PDO::FETCH_ASSOC);

        if (!$reorg) {
            sendJSONResponse(false, null, 'No reorganization history found', 404);
        }

        $sql = "DELETE FROM club_reorganizations WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $reorg['id']]);

        sendJSONResponse(true, null, 'Most recent reorganization deleted successfully');
    } else {
        sendJSONResponse(false, null, 'Invalid request method', 405);
    }
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}
