<?php
require 'database.php';
$res = $conn->query("SELECT DISTINCT district FROM club_register WHERE district != '' LIMIT 5");
while ($r = $res->fetch_assoc()) {
    $d = $r['district'];
    echo $d . " => hex: " . bin2hex($d) . PHP_EOL;
}
