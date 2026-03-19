<?php

/**
 * Clubs API
 * Handles club registration with transaction support
 */

header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';
require_once '../includes/auth.php';

// Require login for all operations
requireLogin();

// Restrict write operations (POST, PUT, DELETE) to admins only
if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
    requireAdmin();
}

try {
    $pdo = getDBConnection();

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            handleClubRegistration($pdo, false);
            break;
        case 'PUT':
            handleClubRegistration($pdo, true);
            break;
        case 'GET':
            handleGetClub($pdo);
            break;
        case 'DELETE':
            handleDeleteClub($pdo);
            break;
        default:
            sendJSONResponse(false, null, 'Invalid request method', 405);
    }
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}

/**
 * Handle club registration
 */
function handleClubRegistration($pdo, $isUpdate = false)
{
    // Get input data based on method
    if ($isUpdate) {
        // For PUT requests, parse input from php://input
        parse_str(file_get_contents('php://input'), $_PUT);
        $data = $_PUT;

        // Get club ID from URL
        $clubId = $_GET['id'] ?? null;
        if (empty($clubId)) {
            sendJSONResponse(false, null, 'Club ID is required for update', 400);
        }
    } else {
        // For POST requests, use $_POST
        $data = $_POST;
        $clubId = null;
    }

    // Validate and sanitize inputs
    $regNumber = sanitizeInput($data['reg_number'] ?? '');
    $clubName = sanitizeInput($data['club_name'] ?? '');
    $districtId = $data['district_id'] ?? null;
    $divisionId = $data['division_id'] ?? null;
    $gsDivisionId = $data['gs_division_id'] ?? null;

    $chairmanName = sanitizeInput($data['chairman_name'] ?? '');
    $chairmanAddress = sanitizeInput($data['chairman_address'] ?? '');
    $chairmanPhone = sanitizeInput($data['chairman_phone'] ?? '');

    $secretaryName = sanitizeInput($data['secretary_name'] ?? '');
    $secretaryAddress = sanitizeInput($data['secretary_address'] ?? '');
    $secretaryPhone = sanitizeInput($data['secretary_phone'] ?? '');

    $dateEntryType = $data['date_entry_type'] ?? 'auto';
    $registrationDate = $data['registration_date'] ?? null;

    $equipment = json_decode($data['equipment'] ?? '[]', true);
    $reorganizations = json_decode($data['reorganizations'] ?? '[]', true);
    $gsDivisionId = empty($gsDivisionId) ? null : $gsDivisionId;

    // Auto-create divisions and GN divisions if they don't exist (when names are provided instead of IDs)
    if ($divisionId !== null && !is_numeric($divisionId)) {
        $divisionId = getOrCreateDivision($pdo, $divisionId, $districtId);
    }

    if ($gsDivisionId !== null && !is_numeric($gsDivisionId)) {
        $gsDivisionId = getOrCreateGSDivision($pdo, $gsDivisionId, $divisionId);
    }

    // Validate that numeric IDs actually exist in database
    if ($divisionId !== null && is_numeric($divisionId)) {
        $stmt = $pdo->prepare("SELECT id FROM divisions WHERE id = :id");
        $stmt->execute(['id' => $divisionId]);
        if (!$stmt->fetch()) {
            $errors[] = 'Invalid division ID';
        }
    }

    if ($gsDivisionId !== null && is_numeric($gsDivisionId)) {
        $stmt = $pdo->prepare("SELECT id FROM grama_niladhari_divisions WHERE id = :id");
        $stmt->execute(['id' => $gsDivisionId]);
        if (!$stmt->fetch()) {
            // If GN Division ID doesn't exist, set to null instead of failing
            $gsDivisionId = null;
        }
    }

    // Auto-create equipment types if they don't exist
    if (is_array($equipment)) {
        foreach ($equipment as &$eq) {
            if (isset($eq['equipment_type_id']) && !is_numeric($eq['equipment_type_id'])) {
                $eq['equipment_type_id'] = getOrCreateEquipmentType($pdo, $eq['equipment_type_id']);
            }
        }
        unset($eq);
    }

    // Validation
    $errors = [];

    // Validate registration number
    if (empty($regNumber)) {
        $errors[] = 'Registration number is required';
    } elseif (!validateRegNumberFormat($regNumber)) {
        $errors[] = 'Invalid registration number format';
    } else {
        // Check uniqueness (skip if updating same club)
        $stmt = $pdo->prepare("SELECT id FROM clubs WHERE reg_number = :reg_number AND id != :club_id");
        $stmt->execute([
            'reg_number' => $regNumber,
            'club_id' => $clubId ?? 0
        ]);
        if ($stmt->fetch()) {
            $errors[] = 'Registration number already exists';
        }
    }

    // Validate club name
    if (empty($clubName)) {
        $errors[] = 'Club name is required';
    }

    // Validate location
    if (empty($districtId)) {
        $errors[] = 'District is required';
    }
    if (empty($divisionId)) {
        $errors[] = 'Division is required';
    }
    // GN Division is optional

    // Validate chairman info
    if (!empty($chairmanPhone) && !validatePhone($chairmanPhone)) {
        $errors[] = 'Chairman phone must be exactly 10 digits';
    }

    // Validate secretary info
    if (!empty($secretaryPhone) && !validatePhone($secretaryPhone)) {
        $errors[] = 'Secretary phone must be exactly 10 digits';
    }

    // Validate date
    if ($dateEntryType === 'manual') {
        if (empty($registrationDate)) {
            $errors[] = 'Registration date is required when manual entry is selected';
        } elseif (!validateDate($registrationDate, false)) {
            $errors[] = 'Invalid registration date or date is in the future';
        }
    } else {
        // Use current date
        $registrationDate = date('Y-m-d');
    }

    // Validate equipment quantities if provided
    if (is_array($equipment)) {
        foreach ($equipment as $eq) {
            if (isset($eq['quantity']) && $eq['quantity'] < 1) {
                $errors[] = 'Equipment quantity must be at least 1';
                break;
            }
        }
    }

    // If there are validation errors, return them
    if (!empty($errors)) {
        sendJSONResponse(false, null, implode(', ', $errors), 400);
    }

    // Start transaction
    $pdo->beginTransaction();

    try {
        if ($isUpdate) {
            // Update existing club
            $sql = "UPDATE clubs SET
                reg_number = :reg_number,
                name = :name,
                registration_date = :registration_date,
                date_entry_type = :date_entry_type,
                chairman_name = :chairman_name,
                chairman_address = :chairman_address,
                chairman_phone = :chairman_phone,
                secretary_name = :secretary_name,
                secretary_address = :secretary_address,
                secretary_phone = :secretary_phone,
                gn_division_id = :gs_division_id
            WHERE id = :club_id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'reg_number' => $regNumber,
                'name' => $clubName,
                'registration_date' => $registrationDate,
                'date_entry_type' => $dateEntryType,
                'chairman_name' => $chairmanName,
                'chairman_address' => $chairmanAddress,
                'chairman_phone' => $chairmanPhone,
                'secretary_name' => $secretaryName,
                'secretary_address' => $secretaryAddress,
                'secretary_phone' => $secretaryPhone,
                'gs_division_id' => empty($gsDivisionId) ? null : $gsDivisionId,
                'club_id' => $clubId
            ]);
        } else {
            // Insert club
            $sql = "INSERT INTO clubs (
                reg_number, name, registration_date, date_entry_type,
                chairman_name, chairman_address, chairman_phone,
                secretary_name, secretary_address, secretary_phone,
                gn_division_id
            ) VALUES (
                :reg_number, :name, :registration_date, :date_entry_type,
                :chairman_name, :chairman_address, :chairman_phone,
                :secretary_name, :secretary_address, :secretary_phone,
                :gs_division_id
            )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'reg_number' => $regNumber,
                'name' => $clubName,
                'registration_date' => $registrationDate,
                'date_entry_type' => $dateEntryType,
                'chairman_name' => $chairmanName,
                'chairman_address' => $chairmanAddress,
                'chairman_phone' => $chairmanPhone,
                'secretary_name' => $secretaryName,
                'secretary_address' => $secretaryAddress,
                'secretary_phone' => $secretaryPhone,
                'gs_division_id' => empty($gsDivisionId) ? null : $gsDivisionId
            ]);

            $clubId = $pdo->lastInsertId();
        }

        // Handle reorganization dates
        if ($isUpdate) {
            // Delete existing reorganization dates
            $deleteReorg = $pdo->prepare("DELETE FROM club_reorganizations WHERE club_id = :club_id");
            $deleteReorg->execute(['club_id' => $clubId]);
        }

        // Insert reorganization dates if provided
        if (is_array($reorganizations) && count($reorganizations) > 0) {
            $reorgSql = "INSERT INTO club_reorganizations (club_id, reorg_date) VALUES (:club_id, :reorg_date)";
            $reorgStmt = $pdo->prepare($reorgSql);

            foreach ($reorganizations as $reorg) {
                if (isset($reorg['date']) && !empty($reorg['date'])) {
                    // Validate date format and not in future
                    if (validateDate($reorg['date'], false)) {
                        $reorgStmt->execute([
                            'club_id' => $clubId,
                            'reorg_date' => $reorg['date']
                        ]);
                    }
                }
            }
        }

        // Commit transaction
        $pdo->commit();

        // Return success with club ID
        $successMessage = $isUpdate ? 'Club updated successfully' : 'Club registered successfully';
        sendJSONResponse(true, [
            'club_id' => $clubId,
            'reg_number' => $regNumber
        ], $successMessage);
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Get existing division ID or create new one
 */
function getOrCreateDivision($pdo, $divisionName, $districtId)
{
    $divisionName = sanitizeInput($divisionName);

    // Check if division already exists
    $stmt = $pdo->prepare("SELECT id FROM divisions WHERE name = :name AND district_id = :district_id");
    $stmt->execute(['name' => $divisionName, 'district_id' => $districtId]);
    $result = $stmt->fetch();

    if ($result) {
        return $result['id'];
    }

    // Create new division
    $stmt = $pdo->prepare("INSERT INTO divisions (name, district_id) VALUES (:name, :district_id)");
    $stmt->execute(['name' => $divisionName, 'district_id' => $districtId]);

    return $pdo->lastInsertId();
}

/**
 * Get existing GN division ID or create new one
 */
function getOrCreateGSDivision($pdo, $gsDivisionName, $divisionId)
{
    $gsDivisionName = sanitizeInput($gsDivisionName);

    // Check if GN division already exists
    $stmt = $pdo->prepare("SELECT id FROM grama_niladhari_divisions WHERE name = :name AND division_id = :division_id");
    $stmt->execute(['name' => $gsDivisionName, 'division_id' => $divisionId]);
    $result = $stmt->fetch();

    if ($result) {
        return $result['id'];
    }

    // Create new GN division
    $stmt = $pdo->prepare("INSERT INTO grama_niladhari_divisions (name, division_id) VALUES (:name, :division_id)");
    $stmt->execute(['name' => $gsDivisionName, 'division_id' => $divisionId]);

    return $pdo->lastInsertId();
}

/**
 * Get existing equipment type ID or create new one
 */
function getOrCreateEquipmentType($pdo, $equipmentTypeName)
{
    $equipmentTypeName = sanitizeInput($equipmentTypeName);

    // Check if equipment type already exists
    $stmt = $pdo->prepare("SELECT id FROM equipment_types WHERE name = :name");
    $stmt->execute(['name' => $equipmentTypeName]);
    $result = $stmt->fetch();

    if ($result) {
        return $result['id'];
    }

    // Create new equipment type (non-standard)
    $stmt = $pdo->prepare("INSERT INTO equipment_types (name, is_standard) VALUES (:name, 0)");
    $stmt->execute(['name' => $equipmentTypeName]);

    return $pdo->lastInsertId();
}

// Get club by ID for editing
function handleGetClub($pdo)
{
    if (!isset($_GET['id'])) {
        sendJSONResponse(false, null, 'Club ID is required', 400);
    }

    $clubId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$clubId) {
        sendJSONResponse(false, null, 'Invalid club ID', 400);
    }

    try {
        // Get club details
        $stmt = $pdo->prepare("
            SELECT 
                c.id, c.reg_number, c.name,
                c.registration_date, c.date_entry_type,
                c.gn_division_id as gs_division_id,
                c.chairman_name, c.chairman_address, c.chairman_phone,
                c.secretary_name, c.secretary_address, c.secretary_phone,
                gn.division_id AS division_id,
                d.district_id AS district_id
            FROM clubs c
            LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
            LEFT JOIN divisions d ON gn.division_id = d.id
            WHERE c.id = :id
        ");
        $stmt->execute(['id' => $clubId]);
        $club = $stmt->fetch();

        if (!$club) {
            sendJSONResponse(false, null, 'Club not found', 404);
        }

        // Get club equipment
        $stmt = $pdo->prepare("
            SELECT et.id, et.name, ce.quantity
            FROM club_equipment ce
            INNER JOIN equipment_types et ON ce.equipment_type_id = et.id
            WHERE ce.club_id = :club_id
        ");
        $stmt->execute(['club_id' => $clubId]);
        $equipment = $stmt->fetchAll();

        $club['equipment'] = $equipment;

        // Get club reorganization dates
        $stmt = $pdo->prepare("
            SELECT id, reorg_date
            FROM club_reorganizations
            WHERE club_id = :club_id
            ORDER BY reorg_date DESC
        ");
        $stmt->execute(['club_id' => $clubId]);
        $reorganizations = $stmt->fetchAll();

        $club['reorganizations'] = $reorganizations;

        sendJSONResponse(true, $club, 'Club details retrieved successfully');
    } catch (PDOException $e) {
        sendJSONResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}

// Delete club
function handleDeleteClub($pdo)
{
    if (!isset($_GET['id'])) {
        sendJSONResponse(false, null, 'Club ID is required', 400);
    }

    $clubId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$clubId) {
        sendJSONResponse(false, null, 'Invalid club ID', 400);
    }

    try {
        $pdo->beginTransaction();

        // Check if club exists
        $stmt = $pdo->prepare("SELECT id, name FROM clubs WHERE id = :id");
        $stmt->execute(['id' => $clubId]);
        $club = $stmt->fetch();

        if (!$club) {
            $pdo->rollBack();
            sendJSONResponse(false, null, 'Club not found', 404);
        }

        // Delete club equipment first (foreign key constraint)
        $stmt = $pdo->prepare("DELETE FROM club_equipment WHERE club_id = :club_id");
        $stmt->execute(['club_id' => $clubId]);

        // Delete club
        $stmt = $pdo->prepare("DELETE FROM clubs WHERE id = :id");
        $stmt->execute(['id' => $clubId]);

        $pdo->commit();
        sendJSONResponse(true, ['id' => $clubId], 'Club deleted successfully');
    } catch (PDOException $e) {
        $pdo->rollBack();
        sendJSONResponse(false, null, 'Database error: ' . $e->getMessage(), 500);
    }
}
