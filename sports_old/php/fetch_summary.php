<?php
require 'database.php';
$district = trim($_GET['district'] ?? '');
$year = trim($_GET['year'] ?? '');

// Quick debug endpoint: ?debug=1
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    $d = $district;
    $escaped = $conn->real_escape_string($d);
    $res = $conn->query("SELECT COUNT(*) AS c FROM club_register WHERE district='" . $escaped . "'");
    $cnt = $res ? $res->fetch_assoc()['c'] : null;
    echo json_encode(['district_received' => $d, 'length' => mb_strlen($d), 'hex' => bin2hex($d), 'count_in_db' => $cnt]);
    $conn->close();
    exit;
}
if ($district === '' || $year === '') {
    echo json_encode(['error' => 'Invalid input: district and year required.']);
    exit;
}

// Validate year (expect 4-digit year)
if (!preg_match('/^\d{4}$/', $year)) {
    echo json_encode(['error' => 'Invalid year format. Use YYYY.']);
    exit;
}

$year = (int)$year;

$sql = "SELECT
            cr.division,
            COUNT(DISTINCT cr.reg_id) AS total_clubs,
            SUM(CASE WHEN YEAR(cr.reg_date) = ? THEN 1 ELSE 0 END) AS reg_in_year,
            SUM(CASE WHEN YEAR(cro.reorg_date) = ? THEN 1 ELSE 0 END) AS reco_in_year
        FROM
            club_register cr
        LEFT JOIN
            club_reorg cro ON cro.reg_id COLLATE utf8mb4_general_ci = cr.reg_id
        WHERE
            cr.district = ?
        GROUP BY
            cr.division";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'Prepare failed', 'detail' => $conn->error]);
    $conn->close();
    exit;
}

$bindOk = $stmt->bind_param("iis", $year, $year, $district);
if ($bindOk === false) {
    echo json_encode(['error' => 'Bind failed', 'detail' => $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$execOk = $stmt->execute();
if ($execOk === false) {
    echo json_encode(['error' => 'Execute failed', 'detail' => $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$result = $stmt->get_result();
if ($result === false) {
    echo json_encode(['error' => 'get_result failed', 'detail' => $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $row['division'] = $row['division'] . ' ප්‍රාදේශීය ලේකම් කොට්ඨාසය';
    $data[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($data);
