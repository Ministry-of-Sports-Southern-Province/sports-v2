<?php
require 'database.php';

if (isset($_GET['reg_id'])) {
    $reg_id = $_GET['reg_id'];

    $reg_id = $conn->real_escape_string($reg_id);

    $stmt = $conn->prepare("SELECT reg_id FROM `club_register` WHERE reg_id = ?");
    $stmt->bind_param("s", $reg_id);
    $stmt->execute();
    $stmt->store_result();

    echo json_encode(['exists' => $stmt->num_rows > 0]);

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'Invalid request']);
}
?>
