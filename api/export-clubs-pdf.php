<?php

/**
 * Export Clubs to PDF API
 * Generates PDF report of clubs based on filters with multilingual support
 */

@set_time_limit(120);
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
        sendJSONResponse(false, null, 'Too many rows to export. Please apply filters.', 400);
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
    $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($clubs)) {
        sendJSONResponse(false, null, 'No data to export', 400);
    }

    // Determine text direction and font for language
    $dir = ($language === 'ta') ? 'rtl' : 'ltr';
    $fontFamily = ($language === 'si' || $language === 'ta') ? '"Noto Sans Sinhala", "Noto Sans Tamil", sans-serif' : 'Arial, sans-serif';

    // Generate HTML table for PDF
    $html = '<meta charset="UTF-8"><style>
        @page { size: A4 landscape; margin: 10mm; }
        body { 
            font-family: ' . $fontFamily . '; 
            margin: 0; 
            padding: 10mm;
            direction: ' . $dir . ';
            box-sizing: border-box;
            background: white;
        }
        .report-wrapper {
            border: 3px double #1e3a8a;
            padding: 20px;
            min-height: 180mm;
            position: relative;
            box-sizing: border-box;
        }
        .print-header { 
            text-align: center; 
            margin-bottom: 25px; 
            border-bottom: 2px solid #1e3a8a; 
            padding-bottom: 15px; 
        }
        .dept-name { 
            font-size: 12pt; 
            font-weight: bold; 
            color: #4b5563; 
            text-transform: uppercase; 
            margin-bottom: 8px; 
        }
        .print-header h1 { 
            font-size: 24pt; 
            font-weight: 900; 
            color: #1e3a8a; 
            text-transform: uppercase; 
            margin: 5px 0; 
            letter-spacing: 1px; 
        }
        .report-info {
            font-size: 11pt;
            color: #374151;
            margin-top: 10px;
            font-weight: 500;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
            border: 1px solid #1e3a8a;
        }
        th { 
            background-color: #1e3a8a; 
            color: white; 
            padding: 8px 4px; 
            text-align: center; 
            border: 1px solid #1e3a8a; 
            font-weight: bold; 
            font-size: 9pt; 
            text-transform: uppercase;
        }
        td { 
            padding: 6px 4px; 
            border: 1px solid #d1d5db; 
            font-size: 8.5pt; 
            color: #000;
        }
        tr:nth-child(even) { 
            background-color: #f9fafb; 
        }
        .signatures { 
            display: table;
            width: 100%;
            margin-top: 50px; 
        }
        .sig-block { 
            display: table-cell;
            width: 50%; 
            text-align: center; 
        }
        .sig-line { 
            border-bottom: 1.5px dotted #1e3a8a; 
            width: 200px;
            margin: 0 auto 8px auto;
            height: 30px; 
        }
        .sig-label { 
            font-size: 10pt; 
            font-weight: bold; 
            color: #1e3a8a; 
            text-transform: uppercase; 
        }
        .footer-bottom { 
            margin-top: 30px; 
            border-top: 2px solid #1e3a8a; 
            padding-top: 8px; 
            text-align: center; 
            font-size: 8pt; 
            color: #555; 
        }
    </style>';

    $html .= '<div class="report-wrapper">';
    $html .= '<div class="print-header">';
    $html .= '<div class="dept-name">' . htmlspecialchars(t('header.department_name', 'Department of Sports Southern Province')) . '</div>';
    $html .= '<h1>' . htmlspecialchars(t('page.dashboard_title', 'Sports Clubs Report')) . '</h1>';
    $html .= '<div class="report-info">';
    $html .= htmlspecialchars(t('table.total', 'Total')) . ': ' . count($clubs) . ' | ';
    $html .= htmlspecialchars(t('report.generated_date', 'Generated')) . ': ' . date('Y-m-d H:i');
    $html .= '</div>';
    $html .= '</div>';

    $html .= '<table>';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th style="width: 8%">' . htmlspecialchars(t('table.reg_number', 'Reg No')) . '</th>';
    $html .= '<th style="width: 8%">' . htmlspecialchars(t('table.registration_date', 'Reg Date')) . '</th>';
    $html .= '<th style="width: 12%">' . htmlspecialchars(t('table.club_name', 'Club Name')) . '</th>';
    $html .= '<th style="width: 8%">' . htmlspecialchars(t('table.division', 'Division')) . '</th>';
    $html .= '<th style="width: 8%">' . htmlspecialchars(t('table.gn_division', 'GN')) . '</th>';
    $html .= '<th style="width: 14%">' . htmlspecialchars(t('table.chairman', 'Chairman')) . '</th>';
    $html .= '<th style="width: 14%">' . htmlspecialchars(t('table.secretary_name', 'Secretary')) . '</th>';
    $html .= '<th style="width: 10%">' . htmlspecialchars(t('table.last_reorg_date', 'Last Reorg')) . '</th>';
    $html .= '<th style="width: 10%">' . htmlspecialchars(t('table.next_reorg_due_date', 'Next Due')) . '</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    foreach ($clubs as $club) {
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

        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($club['reg_number']) . '</td>';
        $html .= '<td>' . htmlspecialchars(date('Y-m-d', strtotime($club['registration_date']))) . '</td>';
        $html .= '<td>' . htmlspecialchars($club['name']) . '</td>';
        $html .= '<td>' . htmlspecialchars($club['division_name'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($club['gn_division_name'] ?? '') . '</td>';
        $html .= '<td>' . htmlspecialchars($club['chairman_name'] ?? '') . '<br><small style="color:#666; font-size:7pt;">' . htmlspecialchars($club['chairman_address'] ?? '') . ' ' . ($club['chairman_phone'] ? '(' . htmlspecialchars($club['chairman_phone']) . ')' : '') . '</small></td>';
        $html .= '<td>' . htmlspecialchars($club['secretary_name'] ?? '') . '<br><small style="color:#666; font-size:7pt;">' . htmlspecialchars($club['secretary_address'] ?? '') . ' ' . ($club['secretary_phone'] ? '(' . htmlspecialchars($club['secretary_phone']) . ')' : '') . '</small></td>';
        $html .= '<td>' . htmlspecialchars($club['last_reorg_date'] ? date('Y-m-d', strtotime($club['last_reorg_date'])) : '-') . '</td>';
        $html .= '<td>' . htmlspecialchars($nextReorgDate ?: '-') . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody>';
    $html .= '</table>';

    $html .= '<div class="signatures">';
    $html .= '<div class="sig-block"><div class="sig-line"></div><div class="sig-label">' . htmlspecialchars(t('footer.created_by', 'Created By')) . '</div></div>';
    $html .= '<div class="sig-block"><div class="sig-line"></div><div class="sig-label">' . htmlspecialchars(t('footer.approved_by', 'Approved By')) . '</div></div>';
    $html .= '</div>';

    $html .= '<div class="footer-bottom">';
    $html .= '<p>' . htmlspecialchars(t('footer.copyright', 'Department of Sports Southern Province © 2026. All rights reserved.')) . '</p>';
    $html .= '</div>';
    $html .= '</div>'; // report-wrapper closing


    // Return HTML that can be processed by html2pdf
    sendJSONResponse(true, ['html' => $html], 'PDF data generated successfully', 200);
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}