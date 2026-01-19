<?php
include 'database.php';

$searchTerm = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';
$regDate = isset($_GET['reg_date']) ? trim($_GET['reg_date']) : '';
$recoDate = isset($_GET['reco_date']) ? trim($_GET['reco_date']) : '';
$district = isset($_GET['dist']) ? trim($_GET['dist']) : '';
$division = isset($_GET['divi']) ? trim($_GET['divi']) : '';

$query = "SELECT reg_id, reg_date, club_name, district, division,village, chair_name, sec_name, reco_date FROM club_register WHERE 1=1";
$params = [];
$types = "";

if (!empty($searchTerm)) {
    $query .= " AND (club_name LIKE ? OR reg_id LIKE ?)";
    $types .= "ss";
    $params[] = "%" . $searchTerm . "%";
    $params[] = "%" . $searchTerm . "%";
}

if (!empty($regDate)) {
    $query .= " AND DATE_FORMAT(reg_date, '%Y-%m') = ?";
    $types .= "s";
    $params[] = $regDate;
}
if (!empty($recoDate)) {
    $query .= " AND DATE_FORMAT(reco_date, '%Y-%m') = ?";
    $types .= "s";
    $params[] = $recoDate;
}

if (!empty($district)) {
    $query .= " AND district = ?";
    $types .= "s";
    $params[] = $district;
}

if (!empty($division)) {
    $query .= " AND division = ?";
    $types .= "s";
    $params[] = $division;
}

$query .= " ORDER BY reg_date DESC";

$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Error preparing query: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $counter = 1; 
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $counter++ . "</td>"; 
        echo "<td>" . htmlspecialchars($row['reg_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['club_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['division']) . "</td>";
        echo "<td>" . htmlspecialchars($row['village']) . "</td>";
        echo "<td><button type='button' class='btn btn-primary edit-button' data-id='" . htmlspecialchars($row['reg_id']) . "'>Edit</button></td>";
        echo "<td><button type='button' class='btn btn-danger delete-button' data-id='" . htmlspecialchars($row['reg_id']) . "'>Delete</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>No results found</td></tr>";
}

$stmt->close();
$conn->close();
?>
