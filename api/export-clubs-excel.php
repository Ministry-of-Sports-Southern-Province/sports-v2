<?php

/**
 * Export Clubs to Excel API
 * Server-side Excel export using CSV format (compatible with Excel)
 */

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
    function t($key, $default = '') {
        global $translations;
        return $translations[$key] ?? $default;
    }

    // Build query
    $sql = "SELECT 
                c.id,
                c.reg_number,
                c.name,
                c.registration_date,
                c.chairman_name,
                c.chairman_address,
                d.name as district_name,
                dv.name as division_name,
                gn.name as gn_division_name
            FROM clubs c
            LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
            LEFT JOIN divisions dv ON gn.division_id = dv.id
            LEFT JOIN districts d ON dv.district_id = d.id
            WHERE 1=1";

    $params = [];

    // Add search filter
    if ($search !== '') {
        $sql .= " AND (c.name LIKE ? OR c.reg_number LIKE ? OR c.chairman_name LIKE ?)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    // Add district filter
    if ($districtId) {
        $sql .= " AND d.id = ?";
        $params[] = $districtId;
    }

    // Add division filter
    if ($divisionId) {
        $sql .= " AND dv.id = ?";
        $params[] = $divisionId;
    }

    // Add GN division filter
    if ($gnDivisionId) {
        $sql .= " AND gn.id = ?";
        $params[] = $gnDivisionId;
    }

    $sql .= " ORDER BY c.registration_date DESC, c.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($clubs)) {
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
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Write header row with translated column names
    $headers = [
        t('table.reg_number', 'Registration No'),
        t('table.registration_date', 'Registration Date'),
        t('table.club_name', 'Club Name'),
        t('table.division', 'Division'),
        t('table.gn_division', 'GN Division'),
        t('table.chairman', 'Chairman'),
        t('table.chairman_address', 'Chairman Address'),
    ];
    fputcsv($output, $headers);

    // Write data rows
    foreach ($clubs as $club) {
        $row = [
            $club['reg_number'],
            date('Y-m-d', strtotime($club['registration_date'])),
            $club['name'],
            $club['division_name'] ?? '',
            $club['gn_division_name'] ?? '',
            $club['chairman_name'] ?? '',
            $club['chairman_address'] ?? '',
        ];
        fputcsv($output, $row);
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    exit('Error: ' . $e->getMessage());
}
