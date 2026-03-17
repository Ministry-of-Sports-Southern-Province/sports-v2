<?php

/**
 * Public Locations API
 * No authentication required. Returns districts and divisions for the
 * public club directory filter dropdowns.
 * Supports: type=district | type=division&parent_id=X
 */

header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendJSONResponse(false, null, 'Invalid request method', 405);
    }

    $pdo  = getDBConnection();
    $type = $_GET['type'] ?? '';

    $validTypes = ['district', 'division'];
    if (!in_array($type, $validTypes)) {
        sendJSONResponse(false, null, 'Invalid location type', 400);
    }

    if ($type === 'district') {
        // Keep response schema minimal for public UI and avoid optional-column dependency.
        $stmt = $pdo->prepare("SELECT id, name FROM districts ORDER BY name");
        $stmt->execute();
        sendJSONResponse(true, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    if ($type === 'division') {
        $parentId = isset($_GET['parent_id']) ? (int)$_GET['parent_id'] : null;
        if (!$parentId) {
            sendJSONResponse(false, null, 'parent_id is required for division type', 400);
        }
        $stmt = $pdo->prepare("SELECT id, name FROM divisions WHERE district_id = :parent_id ORDER BY name");
        $stmt->bindValue(':parent_id', $parentId, PDO::PARAM_INT);
        $stmt->execute();
        sendJSONResponse(true, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}
