<?php

/**
 * Equipment Management API
 * Handles year-wise sports equipment tracking for clubs
 * 
 * Operations:
 * - GET: Fetch equipment for a club (optionally filtered by year)
 * - POST: Add new equipment entry
 * - PUT: Update equipment quantity
 * - DELETE: Remove equipment entry
 */

header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';
require_once '../includes/auth.php';

// Require login for all operations
requireLogin();

try {
    $pdo = getDBConnection();
    $method = $_SERVER['REQUEST_METHOD'];

    // Restrict write operations (POST, PUT, DELETE) to admins only
    if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
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
 * Handle GET request - Fetch equipment for a club with year filtering
 * 
 * Query Parameters:
 * - club_id (required): Club ID
 * - year (optional): Filter by specific year (e.g., 2024)
 * - include_year: Include year in response (default: 1)
 */
function handleGetRequest($pdo)
{
    $clubId = $_GET['club_id'] ?? null;
    $year = $_GET['year'] ?? null;
    $includeYear = $_GET['include_year'] ?? '1';

    if (!$clubId) {
        sendJSONResponse(false, null, 'club_id is required', 400);
    }

    // Validate club exists
    $stmt = $pdo->prepare("SELECT id FROM clubs WHERE id = :club_id LIMIT 1");
    $stmt->execute(['club_id' => $clubId]);
    if (!$stmt->fetch()) {
        sendJSONResponse(false, null, 'Club not found', 404);
    }

    $sql = "
        SELECT 
            ce.id,
            ce.club_id,
            ce.equipment_type_id,
            et.name as equipment_name,
            ce.quantity,
            ce.created_at,
            ce.year
        FROM club_equipment ce
        INNER JOIN equipment_types et ON ce.equipment_type_id = et.id
        WHERE ce.club_id = :club_id
    ";

    $params = ['club_id' => $clubId];

    // Filter by specific year if provided
    if ($year) {
        $sql .= " AND ce.year = :year";
        $params['year'] = intval($year);
    }

    // Order by year descending (newest first), then by created_at
    $sql .= " ORDER BY ce.year DESC, ce.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format response
    if ($includeYear !== '1') {
        // Remove year field if not requested
        $equipment = array_map(function ($item) {
            unset($item['year']);
            return $item;
        }, $equipment);
    }

    sendJSONResponse(true, $equipment, 'Equipment retrieved successfully');
}

/**
 * Handle POST request - Add new equipment entry
 * 
 * Body Parameters (JSON):
 * - club_id (required): Club ID
 * - equipment_type_id (required): Equipment type ID
 * - quantity (required): Quantity (must be >= 1)
 * - date (optional): Date in YYYY-MM-DD or YYYY-MM-DD HH:MM:SS format
 */
function handlePostRequest($pdo)
{
    $input = json_decode(file_get_contents('php://input'), true);

    // Debug: check if JSON was decoded
    if ($input === null) {
        sendJSONResponse(false, null, 'Invalid JSON in request body', 400);
    }

    $clubId = isset($input['club_id']) ? (int)$input['club_id'] : null;
    $equipmentTypeId = isset($input['equipment_type_id']) ? (int)$input['equipment_type_id'] : null;
    $quantity = $input['quantity'] ?? null;
    $year = $input['year'] ?? date('Y');
    $date = $input['date'] ?? date('Y-m-d H:i:s');

    // Validate input - provide specific error messages
    if (!$clubId) {
        sendJSONResponse(false, null, 'club_id is required and must be a valid number', 400);
    }
    if (!$equipmentTypeId) {
        sendJSONResponse(false, null, 'equipment_type_id is required and must be a valid number', 400);
    }
    if ($quantity === null || $quantity === '') {
        sendJSONResponse(false, null, 'quantity is required', 400);
    }

    if (!is_numeric($quantity) || $quantity < 1) {
        sendJSONResponse(false, null, 'quantity must be at least 1', 400);
    }

    // Validate club exists
    $stmt = $pdo->prepare("SELECT id FROM clubs WHERE id = :club_id LIMIT 1");
    $stmt->execute(['club_id' => $clubId]);
    if (!$stmt->fetch()) {
        sendJSONResponse(false, null, 'Club not found', 404);
    }

    // Validate equipment type exists
    $stmt = $pdo->prepare("SELECT id FROM equipment_types WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $equipmentTypeId]);
    if (!$stmt->fetch()) {
        sendJSONResponse(false, null, 'Equipment type not found', 404);
    }

    // Validate and format date/time
    $parsedTime = strtotime($date);
    if ($parsedTime === false) {
        $date = date('Y-m-d H:i:s');
    } else {
        $date = date('Y-m-d H:i:s', $parsedTime);
    }

    try {
        $sql = "
            INSERT INTO club_equipment (club_id, equipment_type_id, quantity, year, created_at)
            VALUES (:club_id, :equipment_type_id, :quantity, :year, :created_at)
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'club_id' => $clubId,
            'equipment_type_id' => $equipmentTypeId,
            'quantity' => $quantity,
            'year' => $year,
            'created_at' => $date
        ]);

        $newId = $pdo->lastInsertId();

        // Fetch the newly created equipment
        $stmt = $pdo->prepare("
            SELECT 
                ce.id,
                ce.club_id,
                ce.equipment_type_id,
                et.name as equipment_name,
                ce.quantity,
                ce.created_at,
                ce.year
            FROM club_equipment ce
            INNER JOIN equipment_types et ON ce.equipment_type_id = et.id
            WHERE ce.id = :id
        ");
        $stmt->execute(['id' => $newId]);
        $newEquipment = $stmt->fetch(PDO::FETCH_ASSOC);

        sendJSONResponse(true, $newEquipment, 'Equipment added successfully', 201);
    } catch (PDOException $e) {
        sendJSONResponse(false, null, 'Failed to add equipment: ' . $e->getMessage(), 400);
    }
}

/**
 * Handle PUT request - Update equipment quantity
 * 
 * Body Parameters (JSON):
 * - id (required): Equipment entry ID (ce.id, not equipment_type_id)
 * - quantity (required): New quantity (must be >= 1)
 */
function handlePutRequest($pdo)
{
    $input = json_decode(file_get_contents('php://input'), true);

    $id = isset($input['id']) ? (int)$input['id'] : null;
    $quantity = $input['quantity'] ?? null;
    $year = $input['year'] ?? null;

    // Validate input
    if (!$id || !$quantity) {
        sendJSONResponse(false, null, 'id and quantity are required', 400);
    }

    if (!is_numeric($quantity) || $quantity < 1) {
        sendJSONResponse(false, null, 'quantity must be at least 1', 400);
    }

    // Verify equipment entry exists
    $stmt = $pdo->prepare("SELECT id FROM club_equipment WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    if (!$stmt->fetch()) {
        sendJSONResponse(false, null, 'Equipment entry not found', 404);
    }

    try {
        $sql = "UPDATE club_equipment SET quantity = :quantity";
        $params = ['id' => $id, 'quantity' => $quantity];
        if ($year !== null) {
            $sql .= ", year = :year";
            $params['year'] = $year;
        }
        $sql .= " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // Fetch updated equipment
        $stmt = $pdo->prepare("
            SELECT 
                ce.id,
                ce.club_id,
                ce.equipment_type_id,
                et.name as equipment_name,
                ce.quantity,
                ce.created_at,
                ce.year
            FROM club_equipment ce
            INNER JOIN equipment_types et ON ce.equipment_type_id = et.id
            WHERE ce.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $updated = $stmt->fetch(PDO::FETCH_ASSOC);

        sendJSONResponse(true, $updated, 'Equipment updated successfully');
    } catch (PDOException $e) {
        sendJSONResponse(false, null, 'Failed to update equipment: ' . $e->getMessage(), 400);
    }
}

/**
 * Handle DELETE request - Remove equipment entry
 * 
 * Body Parameters (JSON):
 * - id (required): Equipment entry ID (ce.id)
 */
function handleDeleteRequest($pdo)
{
    $input = json_decode(file_get_contents('php://input'), true);

    $id = isset($input['id']) ? (int)$input['id'] : null;

    if (!$id) {
        sendJSONResponse(false, null, 'id is required', 400);
    }

    // Verify equipment entry exists
    $stmt = $pdo->prepare("SELECT id, club_id FROM club_equipment WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $equipment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$equipment) {
        sendJSONResponse(false, null, 'Equipment entry not found', 404);
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM club_equipment WHERE id = :id");
        $stmt->execute(['id' => $id]);

        sendJSONResponse(true, ['id' => $id], 'Equipment deleted successfully');
    } catch (PDOException $e) {
        sendJSONResponse(false, null, 'Failed to delete equipment: ' . $e->getMessage(), 400);
    }
}
