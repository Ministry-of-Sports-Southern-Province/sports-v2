<?php
require_once __DIR__ . '/../../config/database.php';
try {
    $pdo = getDBConnection();
} catch (Exception $e) {
    echo "DB connect failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Query A: join via divisions->gn->clubs (COUNT DISTINCT)\n";
$sqlA = "SELECT d.name, COUNT(DISTINCT c.id) as cnt
    FROM districts d
    LEFT JOIN divisions dv ON dv.district_id = d.id
    LEFT JOIN grama_niladhari_divisions gn ON gn.division_id = dv.id
    LEFT JOIN clubs c ON c.gn_division_id = gn.id
    GROUP BY d.id, d.name
    ORDER BY cnt DESC
    LIMIT 10";
foreach ($pdo->query($sqlA) as $r) {
    printf("%s => %d\n", $r['name'], $r['cnt']);
}

echo "\nQuery B: direct clubs.district_id (COUNT DISTINCT)\n";
$sqlB = "SELECT d.name, COUNT(DISTINCT c.id) as cnt
    FROM districts d
    LEFT JOIN clubs c ON c.district_id = d.id
    GROUP BY d.id, d.name
    ORDER BY cnt DESC
    LIMIT 10";
foreach ($pdo->query($sqlB) as $r) {
    printf("%s => %d\n", $r['name'], $r['cnt']);
}

echo "\nSample clubs showing gn_division_id vs district_id (first 50)\n";
$sqlC = "SELECT c.id, c.gn_division_id, c.district_id, gn.name AS gn_name, dv.name AS division_name, d.name AS district_from_gn
    FROM clubs c
    LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
    LEFT JOIN divisions dv ON gn.division_id = dv.id
    LEFT JOIN districts d ON dv.district_id = d.id
    LIMIT 50";
$rows = $pdo->query($sqlC)->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    printf("club %d: gn=%s | district_id=%s | district_from_gn=%s | division=%s\n",
        $r['id'], $r['gn_division_id'] ?? 'NULL', $r['district_id'] ?? 'NULL', $r['district_from_gn'] ?? 'NULL', $r['division_name'] ?? 'NULL');
}

// Also show total clubs count
$tot = $pdo->query('SELECT COUNT(*) FROM clubs')->fetchColumn();
echo "\nTotal clubs in DB: $tot\n";
