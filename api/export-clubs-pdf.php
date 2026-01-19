<?php

/**
 * Export Clubs to PDF API
 * Generates PDF report of clubs based on filters with multilingual support
 */

header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendJSONResponse(false, null, 'Invalid request method', 405);
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
        sendJSONResponse(false, null, 'No data to export', 400);
    }

    // Determine text direction and font for language
    $dir = ($language === 'ta') ? 'rtl' : 'ltr';
    $fontFamily = ($language === 'si' || $language === 'ta') ? '"Noto Sans Sinhala", "Noto Sans Tamil", sans-serif' : 'Arial, sans-serif';

    // Generate HTML table for PDF
    $html = '<meta charset="UTF-8"><style>
        body { 
            font-family: ' . $fontFamily . '; 
            margin: 20px; 
            direction: ' . $dir . ';
        }
        h1 { 
            text-align: center; 
            color: #1f2937; 
            margin-bottom: 5px; 
            font-size: 24px;
        }
        p { 
            text-align: center; 
            color: #666; 
            margin-bottom: 20px; 
            font-size: 12px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px; 
        }
        th { 
            background-color: #2563eb; 
            color: white; 
            padding: 12px; 
            text-align: left; 
            border: 1px solid #ddd; 
            font-weight: bold; 
            font-size: 11px; 
        }
        td { 
            padding: 10px; 
            border: 1px solid #ddd; 
            font-size: 10px; 
        }
        tr:nth-child(even) { 
            background-color: #f9fafb; 
        }
        tr:hover { 
            background-color: #f3f4f6; 
        }
        .footer { 
            margin-top: 30px; 
            text-align: center; 
            font-size: 10px; 
            color: #999; 
            border-top: 1px solid #ddd; 
            padding-top: 10px; 
        }
    </style>';

    $html .= '<h1>' . htmlspecialchars(t('page.dashboard_title', 'Sports Clubs Report')) . '</h1>';
    $html .= '<p>' . htmlspecialchars(t('form.registration_date', 'Registration Date')) . ': ' . date('F d, Y H:i') . '</p>';
    $html .= '<p>' . htmlspecialchars(t('table.total', 'Total')) . ': ' . count($clubs) . '</p>';

    $html .= '<table>';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th>' . htmlspecialchars(t('table.reg_number', 'Registration No')) . '</th>';
    $html .= '<th>' . htmlspecialchars(t('table.registration_date', 'Registration Date')) . '</th>';
    $html .= '<th>' . htmlspecialchars(t('table.club_name', 'Club Name')) . '</th>';
    $html .= '<th>' . htmlspecialchars(t('table.division', 'Division')) . '</th>';
    $html .= '<th>' . htmlspecialchars(t('table.gn_division', 'GN Division')) . '</th>';
    $html .= '<th>' . htmlspecialchars(t('table.chairman', 'Chairman')) . '</th>';
    $html .= '<th>' . htmlspecialchars(t('table.chairman_address', 'Chairman Address')) . '</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    foreach ($clubs as $club) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($club['reg_number']) . '</td>';
        $html .= '<td>' . htmlspecialchars(date('Y-m-d', strtotime($club['registration_date']))) . '</td>';
        $html .= '<td>' . htmlspecialchars($club['name']) . '</td>';
        $html .= '<td>' . htmlspecialchars($club['division_name'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($club['gn_division_name'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($club['chairman_name'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($club['chairman_address'] ?? '') . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';
    $html .= '<div class="footer">';
    $html .= '<p>' . htmlspecialchars(t('footer.copyright', 'Southern Province Sports Department © 2026. All rights reserved.')) . '</p>';
    $html .= '</div>';

    // Return HTML that can be processed by html2pdf
    sendJSONResponse(true, ['html' => $html], 'PDF data generated successfully', 200);
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}
