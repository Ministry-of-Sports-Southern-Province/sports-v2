<?php
include 'database.php';

$query = "SELECT reg_id, club_name, district, division, village, chair_name, sec_name, volleyball, net, nb, football, tenis, bat, wicket,equipments FROM club_register ORDER BY reg_date DESC";
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
