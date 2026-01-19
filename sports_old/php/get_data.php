<?php
include 'database.php';

$reg_id = $_GET['reg_id'];

if (isset($reg_id)) {
    $sql = "SELECT * FROM club_register WHERE reg_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $reg_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data) {
        error_log(print_r($data, true)); 
        echo json_encode(['success' => true] + $data);
    } else {
        echo json_encode(['success' => false, 'message' => 'No data found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No reg_id provided']);
}
?>
