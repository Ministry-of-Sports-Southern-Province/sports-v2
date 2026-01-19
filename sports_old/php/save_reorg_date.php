<?php
// save_reorg_date.php - Add or update reorganization date

header('Content-Type: application/json');

require 'database.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['reg_id']) || !isset($input['reorg_date'])) {
        throw new Exception('Missing required fields: reg_id or reorg_date');
    }

    $reg_id = trim($input['reg_id']);
    $reorg_date = trim($input['reorg_date']);

    // Validate date format
    if (!strtotime($reorg_date)) {
        throw new Exception('Invalid date format');
    }

    // Verify club exists
    $check_sql = "SELECT reg_id FROM club_register WHERE reg_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception("Prepare check failed: " . $conn->error);
    }

    $check_stmt->bind_param('s', $reg_id);
    if (!$check_stmt->execute()) {
        throw new Exception("Check execute failed: " . $check_stmt->error);
    }

    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows === 0) {
        throw new Exception('Club not found');
    }
    $check_stmt->close();

    // Check if reorganization date already exists
    $check_reorg_sql = "SELECT reorg_date FROM club_reorg WHERE reg_id = ? AND reorg_date = ?";
    $check_reorg_stmt = $conn->prepare($check_reorg_sql);
    if (!$check_reorg_stmt) {
        throw new Exception("Prepare reorg check failed: " . $conn->error);
    }

    $check_reorg_stmt->bind_param('ss', $reg_id, $reorg_date);
    if (!$check_reorg_stmt->execute()) {
        throw new Exception("Reorg check execute failed: " . $check_reorg_stmt->error);
    }

    $reorg_result = $check_reorg_stmt->get_result();
    if ($reorg_result->num_rows > 0) {
        throw new Exception('මෙම ප්‍රතිසංවිධාන දිනය දැනටමත් පවතී.');
    }
    $check_reorg_stmt->close();

    // Insert reorganization date
    $insert_sql = "INSERT INTO club_reorg (reg_id, reorg_date) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    if (!$insert_stmt) {
        throw new Exception("Prepare insert failed: " . $conn->error);
    }

    $insert_stmt->bind_param('ss', $reg_id, $reorg_date);
    if (!$insert_stmt->execute()) {
        throw new Exception("Insert execute failed: " . $insert_stmt->error);
    }

    $insert_stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'ප්‍රතිසංවිධාන දිනය සාර්ථකව එකතු කරන්න ලදී.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
