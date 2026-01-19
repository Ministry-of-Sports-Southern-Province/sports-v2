<?php
// fetch_reorg_data.php - Fetch all clubs with their reorganization data

header('Content-Type: application/json');

require 'database.php';

try {
    // Calculate last_reorg_date, reorg_due_date (one year after last_reorg_date)
    // and a status: 'expired' when reorg_due_date <= CURDATE() or no reorg exists, otherwise 'active'
    $sql = "SELECT 
                cr.reg_id,
                cr.club_name,
                cr.district,
                cr.division,
                MAX(cro.reorg_date) AS last_reorg_date,
                DATE_ADD(MAX(cro.reorg_date), INTERVAL 1 YEAR) AS reorg_due_date,
                CASE 
                    WHEN MAX(cro.reorg_date) IS NULL THEN 'expired'
                    WHEN DATE_ADD(MAX(cro.reorg_date), INTERVAL 1 YEAR) <= CURDATE() THEN 'expired'
                    ELSE 'active'
                END AS status
            FROM 
                club_register cr
            LEFT JOIN 
                club_reorg cro ON cro.reg_id COLLATE utf8mb4_general_ci = cr.reg_id COLLATE utf8mb4_general_ci
            GROUP BY 
                cr.reg_id
            ORDER BY 
                cr.district, cr.division, cr.club_name ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Get result failed: " . $stmt->error);
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close();
    echo json_encode($data);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
