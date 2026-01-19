<?php
// delete_reorg_date.php - Delete reorganization record

header('Content-Type: application/json');

require 'database.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['reg_id'])) {
        throw new Exception('Missing required field: reg_id');
    }

    $reg_id = trim($input['reg_id']);

    // Check if reorganization record exists
    $check_sql = "SELECT reorg_date FROM club_reorg WHERE reg_id = ? ORDER BY reorg_date DESC LIMIT 1";
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
        throw new Exception('ප්‍රතිසංවිධාන ඉවත් කිරීම සඳහා ප්‍රතිසංවිධාන ඉතිහාසයක් නොමැත.');
    }

    $row = $check_result->fetch_assoc();
    $reorg_date = $row['reorg_date'];
    $check_stmt->close();

    // Delete the most recent reorganization record
    $delete_sql = "DELETE FROM club_reorg WHERE reg_id = ? AND reorg_date = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    if (!$delete_stmt) {
        throw new Exception("Prepare delete failed: " . $conn->error);
    }

    $delete_stmt->bind_param('ss', $reg_id, $reorg_date);
    if (!$delete_stmt->execute()) {
        throw new Exception("Delete execute failed: " . $delete_stmt->error);
    }

    $delete_stmt->close();

    echo json_encode([
        'success' => true,
        'message' => 'ප්‍රතිසංවිධාන ඉවත් කරන්න ලදී.'
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
