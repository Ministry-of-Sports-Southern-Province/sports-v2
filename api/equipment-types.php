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

    // Restrict PUT (update) to admins only
    if ($method === 'PUT') {
        requireAdmin();
    }

    // Restrict DELETE to admins only
    if ($method === 'DELETE') {
        requireAdmin();
    }

    if ($method === 'GET') {
        handleGetRequest($pdo);
    } elseif ($method === 'POST') {
        handlePostRequest($pdo);
    } elseif ($method === 'PUT') {
        handlePutRequest($pdo);
    } elseif ($method === 'DELETE') {
        handleDeleteRequest($pdo);
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
    // Handle both JSON and form-data requests
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (strpos($contentType, 'application/json') !== false) {
        // Parse JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        $name = trim($input['name'] ?? '');
    } else {
        // Parse form data
        $name = trim($_POST['name'] ?? '');
    }

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

/**
 * Handle PUT request - Update equipment type
 */
function handlePutRequest($pdo)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? (int)$input['id'] : null;
    $name = trim($input['name'] ?? '');

    // Validate input
    if (!$id || empty($name)) {
        sendJSONResponse(false, null, 'ID and name are required', 400);
    }

    try {
        // Check if equipment type exists
        $stmt = $pdo->prepare("SELECT id, is_standard FROM equipment_types WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $equipmentType = $stmt->fetch();

        if (!$equipmentType) {
            sendJSONResponse(false, null, 'Equipment type not found', 404);
        }

        // Check if name already exists (excluding current record)
        $stmt = $pdo->prepare("SELECT id FROM equipment_types WHERE name = :name AND id != :id");
        $stmt->execute(['name' => $name, 'id' => $id]);
        if ($stmt->fetch()) {
            sendJSONResponse(false, null, 'Equipment type name already exists', 409);
        }

        // Update equipment type
        $stmt = $pdo->prepare("UPDATE equipment_types SET name = :name WHERE id = :id");
        $stmt->execute(['name' => $name, 'id' => $id]);

        $result = [
            'id' => $id,
            'name' => $name,
            'is_standard' => (bool)$equipmentType['is_standard']
        ];

        sendJSONResponse(true, $result, 'Equipment type updated successfully');
    } catch (PDOException $e) {
        sendJSONResponse(false, null, 'Failed to update equipment type: ' . $e->getMessage(), 400);
    }
}

/**
 * Handle DELETE request - Delete equipment type
 */
function handleDeleteRequest($pdo)
{
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? (int)$input['id'] : null;

    if (!$id) {
        sendJSONResponse(false, null, 'ID is required', 400);
    }

    try {
        // Check if equipment type exists
        $stmt = $pdo->prepare("SELECT id, is_standard FROM equipment_types WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $equipmentType = $stmt->fetch();

        if (!$equipmentType) {
            sendJSONResponse(false, null, 'Equipment type not found', 404);
        }

        // Prevent deletion of standard equipment types
        if ($equipmentType['is_standard']) {
            sendJSONResponse(false, null, 'Cannot delete standard equipment types', 403);
        }

        // Delete equipment type (CASCADE will delete associated club_equipment records)
        $stmt = $pdo->prepare("DELETE FROM equipment_types WHERE id = :id");
        $stmt->execute(['id' => $id]);

        sendJSONResponse(true, ['id' => $id], 'Equipment type deleted successfully');
    } catch (PDOException $e) {
        sendJSONResponse(false, null, 'Failed to delete equipment type: ' . $e->getMessage(), 400);
    }
}
