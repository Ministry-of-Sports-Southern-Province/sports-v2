<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/database.php';

$reg_id = isset($_GET['reg_id']) ? trim($_GET['reg_id']) : '';
if ($reg_id === '') {
    echo json_encode(['success' => false, 'message' => 'reg_id missing']);
    exit;
}

// Use a join with COLLATE to avoid mismatches when tables use different collations
$sql = "SELECT cro.reorg_date
        FROM club_reorg cro
        JOIN club_register cr ON cro.reg_id COLLATE utf8mb4_general_ci = cr.reg_id COLLATE utf8mb4_general_ci
        WHERE cr.reg_id = ?
        ORDER BY cro.reorg_date DESC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('s', $reg_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($r = $result->fetch_assoc()) {
        $rows[] = $r;
    }
    echo json_encode($rows);
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}

$conn->close();
