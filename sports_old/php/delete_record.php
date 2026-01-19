<?php
include 'database.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $query = "DELETE FROM club_register WHERE reg_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Record deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete the record']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
}

$conn->close();
?>
