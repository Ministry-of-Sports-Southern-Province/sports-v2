<?php
include 'database.php';

$searchTerm = isset($_GET['search_term']) ? trim($_GET['search_term']) : '';
$regDate = isset($_GET['reg_date']) ? trim($_GET['reg_date']) : '';
$recoDate = isset($_GET['reco_date']) ? trim($_GET['reco_date']) : '';
$district = isset($_GET['dist']) ? trim($_GET['dist']) : '';
$division = isset($_GET['divi']) ? trim($_GET['divi']) : '';

$query = "SELECT cr.reg_id, cr.reg_date, cr.club_name, cr.district, cr.division, cr.village, cr.chair_name, cr.sec_name, cro.reorg_due_date
          FROM club_register cr
          LEFT JOIN (
              SELECT reg_id, DATE_ADD(MAX(reorg_date), INTERVAL 1 YEAR) AS reorg_due_date
              FROM club_reorg
              GROUP BY reg_id
          ) cro ON cro.reg_id COLLATE utf8mb4_general_ci = cr.reg_id COLLATE utf8mb4_general_ci
          WHERE 1=1";
$params = [];
$types = "";

if (!empty($searchTerm)) {
    $query .= " AND (cr.club_name LIKE ? OR cr.reg_id LIKE ?)";
    $types .= "ss";
    $params[] = "%" . $searchTerm . "%";
    $params[] = "%" . $searchTerm . "%";
}

if (!empty($regDate)) {
    $query .= " AND DATE_FORMAT(cr.reg_date, '%Y-%m') = ?";
    $types .= "s";
    $params[] = $regDate;
}
if (!empty($recoDate)) {
    $query .= " AND DATE_FORMAT(cro.reorg_due_date, '%Y-%m') = ?";
    $types .= "s";
    $params[] = $recoDate;
}

if (!empty($district)) {
    $query .= " AND cr.district = ?";
    $types .= "s";
    $params[] = $district;
}

if (!empty($division)) {
    $query .= " AND cr.division = ?";
    $types .= "s";
    $params[] = $division;
}

$query .= " ORDER BY cr.reg_date DESC";

$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Error preparing query: " . $conn->error);
}

if (!empty($params)) {
    // bind_param requires parameters to be passed by reference
    $bindNames[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bindNames[] = &$params[$i];
    }
    call_user_func_array([$stmt, 'bind_param'], $bindNames);
}

$stmt->execute();
$result = $stmt->get_result();



if ($result->num_rows > 0) {
    $counter = 1;
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $counter++ . "</td>";
        echo "<td>" . htmlspecialchars($row['reg_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['reg_date']) . "</td>";
        echo "<td>" . htmlspecialchars($row['club_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['division']) . "</td>";
        echo "<td>" . htmlspecialchars($row['village']) . "</td>";
        echo "<td>" . htmlspecialchars($row['chair_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['sec_name']) . "</td>";
        // show reorganization due date (one year after last reorg_date)
        echo "<td>" . htmlspecialchars($row['reorg_due_date'] ?? 'N/A') . "</td>";

        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9'>No results found</td></tr>";
}

$stmt->close();
$conn->close();
