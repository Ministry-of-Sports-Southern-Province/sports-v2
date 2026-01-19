<?php

/**
 * Locations API
 * Handles GET (search) and POST (create) for districts, divisions, and GN divisions
 */

header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';

try {
    $pdo = getDBConnection();
    $method = $_SERVER['REQUEST_METHOD'];

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
 * Handle GET request - Search locations
 */
function handleGetRequest($pdo)
{
    $type = $_GET['type'] ?? '';
    $search = $_GET['search'] ?? '';
    $parentId = $_GET['parent_id'] ?? null;

    // Validate type
    $validTypes = ['district', 'division', 'gn_division'];
    if (!in_array($type, $validTypes)) {
        sendJSONResponse(false, null, 'Invalid location type', 400);
    }

    // Build query based on type
    switch ($type) {
        case 'district':
            $sql = "SELECT id, name, sinhala_letter FROM districts";
            $params = [];

            if ($search !== '') {
                $sql .= " WHERE name LIKE :search";
                $params['search'] = '%' . $search . '%';
            }

            $sql .= " ORDER BY name";
            break;

        case 'division':
            $sql = "SELECT id, name, district_id FROM divisions WHERE 1=1";
            $params = [];

            if ($parentId) {
                $sql .= " AND district_id = :parent_id";
                $params['parent_id'] = $parentId;
            }

            if ($search !== '') {
                $sql .= " AND name LIKE :search";
                $params['search'] = '%' . $search . '%';
            }

            $sql .= " ORDER BY name";
            break;

        case 'gn_division':
            $sql = "SELECT id, name, division_id FROM grama_niladhari_divisions WHERE 1=1";
            $params = [];

            if ($parentId) {
                $sql .= " AND division_id = :parent_id";
                $params['parent_id'] = $parentId;
            }

            if ($search !== '') {
                $sql .= " AND name LIKE :search";
                $params['search'] = '%' . $search . '%';
            }

            $sql .= " ORDER BY name";
            break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    sendJSONResponse(true, $results);
}

/**
 * Handle POST request - Create new location
 */
function handlePostRequest($pdo)
{
    $type = $_POST['type'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $parentId = $_POST['parent_id'] ?? null;

    // Validate type
    $validTypes = ['district', 'division', 'gn_division'];
    if (!in_array($type, $validTypes)) {
        sendJSONResponse(false, null, 'Invalid location type', 400);
    }

    // Validate name
    if (empty($name)) {
        sendJSONResponse(false, null, 'Name is required', 400);
    }

    try {
        switch ($type) {
            case 'district':
                // For district, need to assign sinhala_letter
                // Check if name already exists
                $stmt = $pdo->prepare("SELECT id FROM districts WHERE name = :name");
                $stmt->execute(['name' => $name]);
                if ($stmt->fetch()) {
                    sendJSONResponse(false, null, 'District already exists', 409);
                }

                // Assign sinhala letter based on name or use a default
                // For now, let user districts be created with empty letter
                // or we can prompt them to enter it
                $sinhalaLetter = ''; // Could be enhanced to prompt user

                $stmt = $pdo->prepare("INSERT INTO districts (name, sinhala_letter) VALUES (:name, :letter)");
                $stmt->execute([
                    'name' => $name,
                    'letter' => $sinhalaLetter
                ]);

                $newId = $pdo->lastInsertId();
                $result = [
                    'id' => $newId,
                    'name' => $name,
                    'sinhala_letter' => $sinhalaLetter
                ];
                break;

            case 'division':
                if (!$parentId) {
                    sendJSONResponse(false, null, 'District ID is required', 400);
                }

                // Check for duplicate
                $stmt = $pdo->prepare("SELECT id FROM divisions WHERE name = :name AND district_id = :district_id");
                $stmt->execute([
                    'name' => $name,
                    'district_id' => $parentId
                ]);
                if ($stmt->fetch()) {
                    sendJSONResponse(false, null, 'Division already exists in this district', 409);
                }

                $stmt = $pdo->prepare("INSERT INTO divisions (name, district_id) VALUES (:name, :district_id)");
                $stmt->execute([
                    'name' => $name,
                    'district_id' => $parentId
                ]);

                $newId = $pdo->lastInsertId();
                $result = [
                    'id' => $newId,
                    'name' => $name,
                    'district_id' => $parentId
                ];
                break;

            case 'gn_division':
                if (!$parentId) {
                    sendJSONResponse(false, null, 'Division ID is required', 400);
                }

                // Check for duplicate
                $stmt = $pdo->prepare("SELECT id FROM grama_niladhari_divisions WHERE name = :name AND division_id = :division_id");
                $stmt->execute([
                    'name' => $name,
                    'division_id' => $parentId
                ]);
                if ($stmt->fetch()) {
                    sendJSONResponse(false, null, 'GN Division already exists in this division', 409);
                }

                $stmt = $pdo->prepare("INSERT INTO grama_niladhari_divisions (name, division_id) VALUES (:name, :division_id)");
                $stmt->execute([
                    'name' => $name,
                    'division_id' => $parentId
                ]);

                $newId = $pdo->lastInsertId();
                $result = [
                    'id' => $newId,
                    'name' => $name,
                    'division_id' => $parentId
                ];
                break;
        }

        sendJSONResponse(true, $result, 'Location created successfully');
    } catch (PDOException $e) {
        // Check for duplicate entry error
        if ($e->getCode() == 23000) {
            sendJSONResponse(false, null, 'Duplicate entry', 409);
        }
        throw $e;
    }
}
