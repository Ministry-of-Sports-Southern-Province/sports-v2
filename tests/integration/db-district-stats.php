<?php
require_once __DIR__ . '/../../config/database.php';
$pdo = getDBConnection();

// Counts
$c1 = (int)$pdo->query("SELECT COUNT(*) FROM clubs WHERE gn_division_id IS NULL")->fetchColumn();
$c2 = (int)$pdo->query("SELECT COUNT(*) FROM clubs WHERE gn_division_id IS NOT NULL")->fetchColumn();

echo "clubs with gn_division_id IS NULL: $c1\n";
echo "clubs with gn_division_id IS NOT NULL: $c2\n";

// How many joined to a district via GN produce a district
$c3 = (int)$pdo->query("SELECT COUNT(DISTINCT c.id) FROM clubs c
    LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
    LEFT JOIN divisions dv ON gn.division_id = dv.id
    LEFT JOIN districts d ON dv.district_id = d.id
    WHERE d.id IS NULL")->fetchColumn();

echo "clubs whose GN->division->district join yields NULL district: $c3\n";

// List distinct district names in districts table and counts via the GN chain
foreach ($pdo->query("SELECT d.name, COUNT(DISTINCT c.id) as cnt
    FROM districts d
    LEFT JOIN divisions dv ON dv.district_id = d.id
    LEFT JOIN grama_niladhari_divisions gn ON gn.division_id = dv.id
    LEFT JOIN clubs c ON c.gn_division_id = gn.id
    GROUP BY d.id, d.name
    ORDER BY cnt DESC") as $r) {
    printf("%s => %d\n", $r['name'], $r['cnt']);
}

// Show distinct district_name values coming from clubs-list (simulate)
$stmt = $pdo->query("SELECT DISTINCT COALESCE(d.name, '') as district_name, COUNT(*) as cnt
    FROM clubs c
    LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
    LEFT JOIN divisions dv ON gn.division_id = dv.id
    LEFT JOIN districts d ON dv.district_id = d.id
    GROUP BY district_name
    ORDER BY cnt DESC");

echo "\nDistinct district_name values from join (sample):\n";
foreach ($stmt as $r) {
    printf("'%s' => %d\n", $r['district_name'], $r['cnt']);
}
