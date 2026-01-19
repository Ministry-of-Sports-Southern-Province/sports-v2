<?php
header('Content-Type: application/json');

if (isset($_GET['district'])) {
    $district = $_GET['district'];

    include 'database.php';

    $district = $conn->real_escape_string($district);

    $query = "SELECT division FROM `$district`";
    $result = $conn->query($query);

    $response = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response[] = ['name' => $row['division']];
        }
    }

    echo json_encode($response);
    $conn->close();
} else {
    echo json_encode(['error' => 'Invalid request']);
}

?>
