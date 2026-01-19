<?php
include 'database.php';

$query = "SELECT reg_id, club_name, village, division FROM club_register ORDER BY reg_date DESC";
$result = $conn->query($query);

$data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

echo json_encode($data);
$conn->close();
?>
