<?php
require 'database.php';
$district = 'මාතර';
$res = $conn->query("SELECT COUNT(*) AS c FROM club_register WHERE district='" . $conn->real_escape_string($district) . "'");
if (!$res) {
    echo "Query error: " . $conn->error . PHP_EOL;
    exit(1);
}
$r = $res->fetch_assoc();
echo $r['c'] . PHP_EOL;
