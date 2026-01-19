<?php
include 'database.php';

$query = "SELECT 
            cr.*,
            MAX(cro.reorg_date) AS last_reorg_date,
            DATE_ADD(MAX(cro.reorg_date), INTERVAL 1 YEAR) AS reorg_due_date
        FROM 
            club_register cr
        LEFT JOIN 
            club_reorg cro ON cro.reg_id COLLATE utf8mb4_general_ci = cr.reg_id
        GROUP BY 
            cr.reg_id
        ORDER BY 
            cr.reg_date DESC";
$result = $conn->query($query);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);
$conn->close();
