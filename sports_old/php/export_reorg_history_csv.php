<?php
require_once __DIR__ . '/database.php';

$reg_id = isset($_GET['reg_id']) ? trim($_GET['reg_id']) : '';
if ($reg_id === '') {
    http_response_code(400);
    echo 'reg_id missing';
    exit;
}

$sql = "SELECT cro.reorg_date, cr.club_name, cr.district, cr.division
        FROM club_reorg cro
        LEFT JOIN club_register cr ON cro.reg_id COLLATE utf8mb4_general_ci = cr.reg_id COLLATE utf8mb4_general_ci
        WHERE cro.reg_id = ?
        ORDER BY cro.reorg_date DESC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param('s', $reg_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $filename = 'reorg_history_' . preg_replace('/[^a-zA-Z0-9-_]/', '_', $reg_id) . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // output BOM for Excel compatibility
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, ['reg_id', 'club_name', 'district', 'division', 'reorg_date']);

    while ($row = $result->fetch_assoc()) {
        fputcsv($out, [$reg_id, $row['club_name'], $row['district'], $row['division'], $row['reorg_date']]);
    }

    fclose($out);
    $stmt->close();
    $conn->close();
    exit;
} else {
    http_response_code(500);
    echo 'DB error: ' . $conn->error;
}
