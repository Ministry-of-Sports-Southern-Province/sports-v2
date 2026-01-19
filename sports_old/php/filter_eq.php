<?php
include 'database.php';

$searchTerm = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';
$district = isset($_GET['dist']) ? trim($_GET['dist']) : '';
$division = isset($_GET['divi']) ? trim($_GET['divi']) : '';

$query = "SELECT reg_id, reg_date, club_name, district, division, village, chair_name, sec_name, volleyball, net, nb, football, tenis, bat, wicket,equipments FROM club_register WHERE 1=1";
$params = [];
$types = "";

if (!empty($searchTerm)) {
    $query .= " AND (club_name LIKE ? OR reg_id LIKE ?)";
    $types .= "ss";
    $params[] = "%" . $searchTerm . "%";
    $params[] = "%" . $searchTerm . "%";
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
        echo "<td>" . htmlspecialchars($row['district']) . "</td>";
        echo "<td>" . htmlspecialchars($row['division']) . "</td>";
        echo "<td>" . htmlspecialchars($row['village']) . "</td>";
        echo "<td>" . htmlspecialchars($row['volleyball']) . "</td>";
        echo "<td>" . htmlspecialchars($row['net']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nb']) . "</td>";
        echo "<td>" . htmlspecialchars($row['football']) . "</td>";
        echo "<td>" . htmlspecialchars($row['tenis']) . "</td>";
        echo "<td>" . htmlspecialchars($row['bat']) . "</td>";
        echo "<td>" . htmlspecialchars($row['wicket']) . "</td>";
        echo "<td>" . htmlspecialchars($row['equipments']) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='4'>No results found</td></tr>";
}

$stmt->close();
$conn->close();
?>
