<?php

/**
 * Export Clubs to Excel API
 * Server-side Excel export using CSV format (compatible with Excel)
 */

@set_time_limit(120);
header('Content-Type: text/csv; charset=UTF-8');
require_once '../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        exit('Invalid request method');
    }

    $pdo = getDBConnection();

    $search = $_GET['search'] ?? '';
    $districtId = $_GET['district_id'] ?? null;
    $divisionId = $_GET['division_id'] ?? null;
    $gnDivisionId = $_GET['gn_division_id'] ?? null;
    $language = $_GET['language'] ?? 'si';

    // Load language file
    $langFile = '../assets/lang/' . $language . '.json';
    if (!file_exists($langFile)) {
        $langFile = '../assets/lang/si.json';
    }
    $translations = json_decode(file_get_contents($langFile), true);

    // Helper function to get translation
    function t($key, $default = '')
    {
        global $translations;
        return $translations[$key] ?? $default;
    }

    // Build query
    $baseFrom = "FROM clubs c
            LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
            LEFT JOIN divisions dv ON gn.division_id = dv.id
            LEFT JOIN districts d ON dv.district_id = d.id
            LEFT JOIN club_reorganizations cr ON c.id = cr.club_id
            WHERE 1=1";

    $params = [];

    // Add search filter
    if ($search !== '') {
        $baseFrom .= " AND (c.name LIKE ? OR c.reg_number LIKE ? OR c.chairman_name LIKE ?)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    // Add district filter
    if ($districtId) {
        $baseFrom .= " AND d.id = ?";
        $params[] = $districtId;
    }

    // Add division filter
    if ($divisionId) {
        $baseFrom .= " AND dv.id = ?";
        $params[] = $divisionId;
    }

    // Add GN division filter
    if ($gnDivisionId) {
        $baseFrom .= " AND gn.id = ?";
        $params[] = $gnDivisionId;
    }

    $countSql = "SELECT COUNT(DISTINCT c.id) " . $baseFrom;
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)($countStmt->fetchColumn() ?: 0);

    $maxExport = 5000;
    if ($total > $maxExport) {
        http_response_code(400);
        exit('Too many rows to export. Please apply filters.');
    }

    $sql = "SELECT 
                c.id,
                c.reg_number,
                c.name,
                c.registration_date,
                c.chairman_name,
                c.chairman_address,
                c.chairman_phone,
                c.secretary_name,
                c.secretary_address,
                c.secretary_phone,
                d.name as district_name,
                dv.name as division_name,
                gn.name as gn_division_name,
                MAX(cr.reorg_date) as last_reorg_date
            " . $baseFrom . "
            GROUP BY c.id
            ORDER BY c.registration_date DESC, c.created_at DESC
            LIMIT ?";

    $params[] = $maxExport;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($total === 0) {
        http_response_code(400);
        exit('No data to export');
    }

    // Set appropriate headers for Excel download
    header('Content-Disposition: attachment; filename="clubs_export_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Create CSV output
    $output = fopen('php://output', 'w');

    // Set UTF-8 BOM for proper character encoding in Excel
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // Write header row with translated column names
    $headers = [
        t('table.reg_number', 'Registration No'),
        t('table.registration_date', 'Registration Date'),
        t('table.club_name', 'Club Name'),
        t('table.division', 'Division'),
        t('table.gn_division', 'GN Division'),
        t('table.chairman_name', 'Chairman Name'),
        t('table.chairman_address', 'Chairman Address and Phone'),
        t('table.secretary_name', 'Secretary Name'),
        t('table.secretary_address', 'Secretary Address and Phone'),
        t('table.last_reorg_date', 'Last Reorganization Date'),
        t('table.next_reorg_due_date', 'Next Reorganization Due Date'),
    ];
    fputcsv($output, $headers);

    // Write data rows
    while ($club = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Calculate next reorganization due date
        $nextReorgDate = '';
        if ($club['last_reorg_date']) {
            $lastDate = new DateTime($club['last_reorg_date']);
            $lastMonth = (int)$lastDate->format('m');
            $lastYear = (int)$lastDate->format('Y');
            $lastDay = (int)$lastDate->format('d');

            if ($lastMonth >= 7) {
                // If reorg is in July or later, next due is 2 years later, January 1st
                $nextDate = new DateTime(($lastYear + 2) . '-01-01');
            } else {
                // If reorg is Jan-June, next due is 1 year later, same month/day
                $nextDate = new DateTime(($lastYear + 1) . '-' . str_pad($lastMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($lastDay, 2, '0', STR_PAD_LEFT));
            }
            $nextReorgDate = $nextDate->format('Y-m-d');
        }

        $row = [
            $club['reg_number'],
            date('Y-m-d', strtotime($club['registration_date'])),
            $club['name'],
            $club['division_name'] ?? '',
            $club['gn_division_name'] ?? '',
            $club['chairman_name'] ?? '',
            ($club['chairman_address'] ?? '') . ' ' . ($club['chairman_phone'] ? '(' . $club['chairman_phone'] . ')' : ''),
            $club['secretary_name'] ?? '',
            ($club['secretary_address'] ?? '') . ' ' . ($club['secretary_phone'] ? '(' . $club['secretary_phone'] . ')' : ''),
            $club['last_reorg_date'] ? date('Y-m-d', strtotime($club['last_reorg_date'])) : '',
            $nextReorgDate,
        ];
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
} catch (Exception $e) {
    http_response_code(500);
    exit('Error: ' . $e->getMessage());
}