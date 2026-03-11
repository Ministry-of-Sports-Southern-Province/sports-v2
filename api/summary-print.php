<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';

try {
    $pdo = getDBConnection();

    $currentYear = (int)date('Y');
    $startYear   = $currentYear - 4; // last 5 years inclusive

    // ── 1. Top-level stats ─────────────────────────────────────────────────────
    $total = (int)$pdo->query("SELECT COUNT(*) FROM clubs")->fetchColumn();

    try {
        $sql = "SELECT
            SUM(CASE WHEN (due_date IS NOT NULL AND due_date > CURDATE()) THEN 1 ELSE 0 END) AS active,
            SUM(CASE WHEN (due_date IS NULL OR due_date <= CURDATE()) THEN 1 ELSE 0 END) AS expired
            FROM (
                SELECT c.id,
                    CASE
                        WHEN MAX(cr.reorg_date) IS NULL THEN NULL
                        WHEN MONTH(MAX(cr.reorg_date)) > 6
                             THEN STR_TO_DATE(CONCAT(YEAR(MAX(cr.reorg_date)) + 2, '-01-01'), '%Y-%m-%d')
                        ELSE DATE_ADD(MAX(cr.reorg_date), INTERVAL 1 YEAR)
                    END AS due_date
                FROM clubs c
                LEFT JOIN club_reorganizations cr ON c.id = cr.club_id
                GROUP BY c.id
            ) AS t";
        $row = $pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
        $active  = (int)($row['active']  ?? 0);
        $expired = (int)($row['expired'] ?? 0);
    } catch (Exception $e) {
        $active  = $total;
        $expired = 0;
    }

    try {
        $totalReorgs = (int)$pdo->query("SELECT COUNT(*) FROM club_reorganizations")->fetchColumn();
    } catch (Exception $e) {
        $totalReorgs = 0;
    }

    // ── 2. Yearly registrations (last 5 years) ─────────────────────────────────
    $stmt = $pdo->prepare(
        "SELECT YEAR(registration_date) AS year, COUNT(*) AS count
         FROM clubs
         WHERE YEAR(registration_date) BETWEEN :start AND :end
         GROUP BY YEAR(registration_date)
         ORDER BY year"
    );
    $stmt->execute(['start' => $startYear, 'end' => $currentYear]);
    $rawYearlyReg = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── 3. Yearly reorganizations (last 5 years) ───────────────────────────────
    try {
        $stmt = $pdo->prepare(
            "SELECT YEAR(reorg_date) AS year, COUNT(*) AS count
             FROM club_reorganizations
             WHERE YEAR(reorg_date) BETWEEN :start AND :end
             GROUP BY YEAR(reorg_date)
             ORDER BY year"
        );
        $stmt->execute(['start' => $startYear, 'end' => $currentYear]);
        $rawYearlyReorg = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $rawYearlyReorg = [];
    }

    // ── 4. Monthly registrations (current year, months 1–12) ──────────────────
    $stmt = $pdo->prepare(
        "SELECT MONTH(registration_date) AS month, COUNT(*) AS count
         FROM clubs
         WHERE YEAR(registration_date) = :year
         GROUP BY MONTH(registration_date)
         ORDER BY month"
    );
    $stmt->execute(['year' => $currentYear]);
    $rawMonthlyReg = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── 5. Monthly reorganizations (current year, months 1–12) ───────────────
    try {
        $stmt = $pdo->prepare(
            "SELECT MONTH(reorg_date) AS month, COUNT(*) AS count
             FROM club_reorganizations
             WHERE YEAR(reorg_date) = :year
             GROUP BY MONTH(reorg_date)
             ORDER BY month"
        );
        $stmt->execute(['year' => $currentYear]);
        $rawMonthlyReorg = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $rawMonthlyReorg = [];
    }

    // ── 6. District breakdown (registrations + reorgs per district) ────────────
    try {
        $stmt = $pdo->query(
            "SELECT d.name AS district,
                    COUNT(DISTINCT c.id) AS registrations,
                    COUNT(DISTINCT cr.id) AS reorgs
             FROM districts d
             LEFT JOIN divisions dv ON dv.district_id = d.id
             LEFT JOIN grama_niladhari_divisions gn ON gn.division_id = dv.id
             LEFT JOIN clubs c ON c.gn_division_id = gn.id
             LEFT JOIN club_reorganizations cr ON cr.club_id = c.id
             GROUP BY d.id, d.name
             ORDER BY d.name"
        );
        $districtBreakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($districtBreakdown as &$row) {
            $row['registrations'] = (int)$row['registrations'];
            $row['reorgs']        = (int)$row['reorgs'];
        }
        unset($row);
    } catch (Exception $e) {
        $districtBreakdown = [];
    }

    // ── Helper: normalise to full 5-year array ────────────────────────────────
    function buildYearArray(array $raw, int $startYear, int $endYear): array {
        $map = [];
        foreach ($raw as $r) {
            $map[(int)$r['year']] = (int)$r['count'];
        }
        $out = [];
        for ($y = $startYear; $y <= $endYear; $y++) {
            $out[] = ['year' => $y, 'count' => $map[$y] ?? 0];
        }
        return $out;
    }

    // ── Helper: normalise to full 12-month array ──────────────────────────────
    function buildMonthArray(array $raw): array {
        $map = [];
        foreach ($raw as $r) {
            $map[(int)$r['month']] = (int)$r['count'];
        }
        $out = [];
        for ($m = 1; $m <= 12; $m++) {
            $out[] = ['month' => $m, 'count' => $map[$m] ?? 0];
        }
        return $out;
    }

    $yearlyRegistrations = buildYearArray($rawYearlyReg,   $startYear, $currentYear);
    $yearlyReorgs        = buildYearArray($rawYearlyReorg, $startYear, $currentYear);
    $monthlyRegistrations = buildMonthArray($rawMonthlyReg);
    $monthlyReorgs        = buildMonthArray($rawMonthlyReorg);

    // ── Comparison data: combined registrations vs reorgs per year ────────────
    $comparisonData = [];
    for ($i = 0; $i < count($yearlyRegistrations); $i++) {
        $comparisonData[] = [
            'year'          => $yearlyRegistrations[$i]['year'],
            'registrations' => $yearlyRegistrations[$i]['count'],
            'reorgs'        => $yearlyReorgs[$i]['count'],
        ];
    }

    sendJSONResponse(true, [
        'stats' => [
            'total'       => $total,
            'active'      => $active,
            'expired'     => $expired,
            'totalReorgs' => $totalReorgs,
        ],
        'yearlyRegistrations'  => $yearlyRegistrations,
        'yearlyReorgs'         => $yearlyReorgs,
        'monthlyRegistrations' => $monthlyRegistrations,
        'monthlyReorgs'        => $monthlyReorgs,
        'comparisonData'       => $comparisonData,
        'districtBreakdown'    => $districtBreakdown,
        'currentYear'          => $currentYear,
        'startYear'            => $startYear,
    ]);

} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}
