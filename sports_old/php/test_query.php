<?php
require 'database.php';
$district = 'මාතර';
$year = 2024;
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
    echo "Prepare error: " . $conn->error . PHP_EOL;
    exit(1);
}
$bindOk = $stmt->bind_param("iis", $year, $year, $district);
if ($bindOk === false) {
    echo "Bind error: " . $stmt->error . PHP_EOL;
    exit(1);
}
$execOk = $stmt->execute();
if ($execOk === false) {
    echo "Execute error: " . $stmt->error . PHP_EOL;
    exit(1);
}
$result = $stmt->get_result();
if ($result === false) {
    echo "get_result error: " . $stmt->error . PHP_EOL;
    exit(1);
}
$rows = [];
while ($r = $result->fetch_assoc()) {
    $rows[] = $r;
}
echo "Rows: " . count($rows) . PHP_EOL;
if (count($rows) > 0) {
    print_r(array_slice($rows, 0, 5));
}
