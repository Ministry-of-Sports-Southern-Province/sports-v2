<?php

/**
 * Equipment Types API
 * Handles GET (search) and POST (create) for equipment types
 */

header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';
require_once '../includes/auth.php';

// Require login for all operations
requireLogin();

try {
    $pdo = getDBConnection();
    $method = $_SERVER['REQUEST_METHOD'];

    // Restrict POST (create) to admins only
    if ($method === 'POST') {
        requireAdmin();
    }

    if ($method === 'GET') {
        handleGetRequest($pdo);
    } elseif ($method === 'POST') {
        handlePostRequest($pdo);
    } else {
        sendJSONResponse(false, null, 'Invalid request method', 405);
    }
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}

/**
 * Handle GET request - Search equipment types
 */
function handleGetRequest($pdo)
{
    $search = $_GET['search'] ?? '';
    $isStandard = $_GET['is_standard'] ?? null;

    $sql = "SELECT id, name, is_standard FROM equipment_types WHERE 1=1";
    $params = [];

    // Filter by is_standard if provided
    if ($isStandard !== null) {
        $sql .= " AND is_standard = :is_standard";
        $params['is_standard'] = $isStandard === '1' ? 1 : 0;
    }

    // Search by name
    if ($search !== '') {
        $sql .= " AND name LIKE :search";
        $params['search'] = '%' . $search . '%';
    }

    $sql .= " ORDER BY is_standard DESC, name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendJSONResponse(true, $results);
}

/**
 * Handle POST request - Create new equipment type
 */
function handlePostRequest($pdo)
{
    $name = trim($_POST['name'] ?? '');

    // Validate name
    if (empty($name)) {
        sendJSONResponse(false, null, 'Equipment name is required', 400);
    }

    try {
        // Check if equipment type already exists
        $stmt = $pdo->prepare("SELECT id FROM equipment_types WHERE name = :name");
        $stmt->execute(['name' => $name]);
        if ($stmt->fetch()) {
            sendJSONResponse(false, null, 'Equipment type already exists', 409);
        }

        // Insert new equipment type (is_standard = 0 for custom equipment)
        $stmt = $pdo->prepare("INSERT INTO equipment_types (name, is_standard) VALUES (:name, 0)");
        $stmt->execute(['name' => $name]);

        $newId = $pdo->lastInsertId();
        $result = [
            'id' => $newId,
            'name' => $name,
            'is_standard' => false
        ];

        sendJSONResponse(true, $result, 'Equipment type created successfully');
    } catch (PDOException $e) {
        // Check for duplicate entry error
        if ($e->getCode() == 23000) {
            sendJSONResponse(false, null, 'Equipment type already exists', 409);
        }
        throw $e;
    }
}
