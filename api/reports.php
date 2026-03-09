<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';

try {
    $pdo = getDBConnection();
    $type = $_GET['type'] ?? 'registered';
    $action = $_GET['action'] ?? '';
    $columns = explode(',', $_GET['columns'] ?? 'reg_number,name,district');

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $printAll = isset($_GET['print_all']) && (string)$_GET['print_all'] === '1';
    $maxPrintAll = 5000;

    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 10;
    if ($limit > 500) $limit = 500;
    $offset = ($page - 1) * $limit;

    $data = [];
    $total = 0;

    // Handle get_years action for district statistics
    if ($type === 'district_statistics' && $action === 'get_years') {
        $sql = "SELECT DISTINCT YEAR(c.registration_date) as year 
                FROM clubs c 
                ORDER BY year DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $years = $stmt->fetchAll(PDO::FETCH_COLUMN);
        sendJSONResponse(true, $years);
    }

    if ($type === 'reorganized') {
        $year = (int)($_GET['year'] ?? date('Y'));
        $yearStart = $year . '-01-01';
        $yearEnd = ($year + 1) . '-01-01';
        $district = $_GET['district'] ?? '';

        $baseFrom = "FROM clubs c
                LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
                LEFT JOIN divisions dv ON gn.division_id = dv.id
                LEFT JOIN districts d ON dv.district_id = d.id
                INNER JOIN club_reorganizations cr ON c.id = cr.club_id
                WHERE cr.reorg_date >= :year_start AND cr.reorg_date < :year_end";

        $params = ['year_start' => $yearStart, 'year_end' => $yearEnd];

        if ($district) {
            $baseFrom .= " AND d.name = :district";
            $params['district'] = $district;
        }

        $countSql = "SELECT COUNT(*) " . $baseFrom;
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)($countStmt->fetchColumn() ?: 0);
        if ($printAll && $total > $maxPrintAll) {
            sendJSONResponse(false, null, 'Too many rows to print all. Please apply filters.', 400);
        }

        $sql = "SELECT c.reg_number, c.name, c.chairman_name as chairman, c.chairman_phone, 
                       c.secretary_name as secretary, c.registration_date, d.name as district, 
                       dv.name as division, cr.reorg_date
                " . $baseFrom . "
                ORDER BY cr.reorg_date DESC, c.name";

        if (!$printAll) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        if (!$printAll) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($type === 'registered') {
        $district = $_GET['district'] ?? '';
        $dateRange = $_GET['date_range'] ?? 'all';

        $baseFrom = "FROM clubs c
                LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
                LEFT JOIN divisions dv ON gn.division_id = dv.id
                LEFT JOIN districts d ON dv.district_id = d.id
                WHERE 1=1";

        $params = [];

        if ($district) {
            $baseFrom .= " AND d.name = :district";
            $params['district'] = $district;
        }

        if ($dateRange === 'year') {
            $baseFrom .= " AND c.registration_date >= DATE_FORMAT(CURDATE(), '%Y-01-01')";
            $baseFrom .= " AND c.registration_date < DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-01-01'), INTERVAL 1 YEAR)";
        } elseif ($dateRange === 'month') {
            $baseFrom .= " AND c.registration_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')";
            $baseFrom .= " AND c.registration_date < DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH)";
        }

        $countSql = "SELECT COUNT(*) " . $baseFrom;
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)($countStmt->fetchColumn() ?: 0);
        if ($printAll && $total > $maxPrintAll) {
            sendJSONResponse(false, null, 'Too many rows to print all. Please apply filters.', 400);
        }

        $sql = "SELECT c.reg_number, c.name, c.chairman_name as chairman, c.chairman_phone, 
                       c.secretary_name as secretary, c.registration_date, d.name as district, 
                       dv.name as division
                " . $baseFrom . "
                ORDER BY c.registration_date DESC, c.name";

        if (!$printAll) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        if (!$printAll) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($type === 'equipment') {
        $equipment = $_GET['equipment'] ?? '';
        $district = $_GET['district'] ?? '';
        $division = $_GET['division'] ?? '';
        $gsDivision = $_GET['gs_division'] ?? '';

        $baseFrom = "FROM clubs c
                LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
                LEFT JOIN divisions dv ON gn.division_id = dv.id
                LEFT JOIN districts d ON dv.district_id = d.id
                INNER JOIN club_equipment ce ON c.id = ce.club_id
                INNER JOIN equipment_types et ON ce.equipment_type_id = et.id
                WHERE 1=1";

        $params = [];

        if ($equipment) {
            $baseFrom .= " AND et.name = :equipment";
            $params['equipment'] = $equipment;
        }

        if ($district) {
            $baseFrom .= " AND d.name = :district";
            $params['district'] = $district;
        }

        if ($division) {
            $baseFrom .= " AND dv.name = :division";
            $params['division'] = $division;
        }

        if ($gsDivision) {
            $baseFrom .= " AND gn.name = :gs_division";
            $params['gs_division'] = $gsDivision;
        }

        $countSql = "SELECT COUNT(*) " . $baseFrom;
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)($countStmt->fetchColumn() ?: 0);
        if ($printAll && $total > $maxPrintAll) {
            sendJSONResponse(false, null, 'Too many rows to print all. Please apply filters.', 400);
        }

        $sql = "SELECT c.reg_number, c.name, d.name as district, dv.name as division, gn.name as gs_division, et.name as equipment, ce.quantity
                FROM clubs c
                LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
                LEFT JOIN divisions dv ON gn.division_id = dv.id
                LEFT JOIN districts d ON dv.district_id = d.id
                INNER JOIN club_equipment ce ON c.id = ce.club_id
                INNER JOIN equipment_types et ON ce.equipment_type_id = et.id
                WHERE 1=1";

        // Keep select + where identical to $baseFrom, for safety/clarity
        $sql = "SELECT c.reg_number, c.name, d.name as district, dv.name as division, gn.name as gs_division, et.name as equipment, ce.quantity
                " . $baseFrom . "
                ORDER BY d.name, dv.name, gn.name, c.name, et.name";

        if (!$printAll) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        if (!$printAll) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($type === 'district_statistics') {
        $district = $_GET['district'] ?? '';
        $year = (int)($_GET['year'] ?? date('Y'));
        $yearStart = $year . '-01-01';
        $yearEnd = ($year + 1) . '-01-01';

        $divisionBaseFrom = "FROM divisions dv
                        LEFT JOIN districts d ON dv.district_id = d.id
                        WHERE 1=1";
        $divisionParams = [];

        if ($district) {
            $divisionBaseFrom .= " AND d.name = ?";
            $divisionParams[] = $district;
        }

        $countSql = "SELECT COUNT(DISTINCT dv.id) " . $divisionBaseFrom;
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($divisionParams);
        $total = (int)($countStmt->fetchColumn() ?: 0);
        if ($printAll && $total > $maxPrintAll) {
            sendJSONResponse(false, null, 'Too many rows to print all. Please apply filters.', 400);
        }

        $aggregateFrom = "FROM divisions dv
                LEFT JOIN districts d ON dv.district_id = d.id
                LEFT JOIN grama_niladhari_divisions gn ON gn.division_id = dv.id
                LEFT JOIN clubs c ON c.gn_division_id = gn.id
                LEFT JOIN club_reorganizations cr ON cr.club_id = c.id
                WHERE 1=1";
        $params = [$yearStart, $yearEnd, $yearStart, $yearEnd];

        if ($district) {
            $aggregateFrom .= " AND d.name = ?";
            $params[] = $district;
        }

        $aggregateSelect = "SELECT dv.id, dv.name as division_name,
                COUNT(DISTINCT c.id) as total_clubs,
                COUNT(DISTINCT CASE WHEN c.registration_date >= ? AND c.registration_date < ? THEN c.id END) as year_registered,
                COUNT(DISTINCT CASE WHEN cr.reorg_date >= ? AND cr.reorg_date < ? THEN c.id END) as year_reorganized
                ";

        $aggregateSql = $aggregateSelect . $aggregateFrom . " GROUP BY dv.id, dv.name";

        $sql = $aggregateSql . " ORDER BY dv.name";
        if (!$printAll) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalsSql = "SELECT
                SUM(total_clubs) as total_clubs,
                SUM(year_registered) as total_registered,
                SUM(year_reorganized) as total_reorganized
            FROM (" . $aggregateSql . ") t";
        $totalsStmt = $pdo->prepare($totalsSql);

        // For the totals query, we need the same parameters but without LIMIT/OFFSET
        $totalsParams = array_slice($params, 0, count($params) - ($printAll ? 0 : 2));
        $totalsStmt->execute($totalsParams);
        $totals = $totalsStmt->fetch(PDO::FETCH_ASSOC) ?: ['total_clubs' => 0, 'total_registered' => 0, 'total_reorganized' => 0];
    }

    $totalPages = $limit > 0 ? (int)ceil($total / $limit) : 1;
    if ($page > $totalPages) $page = $totalPages;

    $pagination = [
        'page' => $printAll ? 1 : $page,
        'limit' => $printAll ? $total : $limit,
        'total' => $total,
        'total_pages' => $printAll ? 1 : $totalPages
    ];

    // Add grand totals for district_statistics
    $metadata = [
        'pagination' => $pagination
    ];

    if ($type === 'district_statistics') {
        $metadata['pagination']['totals'] = [
            'total_clubs' => (int)($totals['total_clubs'] ?? 0),
            'total_registered' => (int)($totals['total_registered'] ?? 0),
            'total_reorganized' => (int)($totals['total_reorganized'] ?? 0)
        ];
    }

    sendJSONResponse(true, $data, '', 200, $metadata);
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}
