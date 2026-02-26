<?php

/**
 * Registration Number Validation API
 * Checks if registration number is unique
 */

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
require_once '../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendJSONResponse(false, null, 'Invalid request method', 405);
    }

    $regNumber = trim($_GET['reg_number'] ?? '');

    if (empty($regNumber)) {
        sendJSONResponse(false, null, 'Registration number is required', 400);
    }

    // Validate format: දපස/ක්රීඩා/{ග|ම|හ}-{digits}
    if (!validateRegNumberFormat($regNumber)) {
        sendJSONResponse(false, [
            'available' => false,
            'format_error' => true,
            'debug' => [
                'reg_number' => $regNumber,
                'length' => mb_strlen($regNumber),
                'bytes' => strlen($regNumber),
                'expected_pattern' => 'දපස/ක්රීඩා/[ගා|මා|හ]-[0-9]+'
            ]
        ], 'Invalid registration number format');
    }

    $pdo = getDBConnection();

    // Check if registration number already exists
    $stmt = $pdo->prepare("SELECT id FROM clubs WHERE reg_number = :reg_number");
    $stmt->execute(['reg_number' => $regNumber]);
    $exists = $stmt->fetch();

    $available = !$exists;

    sendJSONResponse(true, ['available' => $available], $available ? 'Available' : 'Already exists');
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}
