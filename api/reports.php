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
        $year = $_GET['year'] ?? date('Y');
        $district = $_GET['district'] ?? '';

        $baseFrom = "FROM clubs c
                LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
                LEFT JOIN divisions dv ON gn.division_id = dv.id
                LEFT JOIN districts d ON dv.district_id = d.id
                INNER JOIN club_reorganizations cr ON c.id = cr.club_id
                WHERE YEAR(cr.reorg_date) = :year";

        $params = ['year' => $year];

        if ($district) {
            $baseFrom .= " AND d.name = :district";
            $params['district'] = $district;
        }

        $countSql = "SELECT COUNT(*) " . $baseFrom;
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)($countStmt->fetchColumn() ?: 0);

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
            $baseFrom .= " AND YEAR(c.registration_date) = YEAR(CURDATE())";
        } elseif ($dateRange === 'month') {
            $baseFrom .= " AND YEAR(c.registration_date) = YEAR(CURDATE()) AND MONTH(c.registration_date) = MONTH(CURDATE())";
        }

        $countSql = "SELECT COUNT(*) " . $baseFrom;
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)($countStmt->fetchColumn() ?: 0);

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
        $gnDivision = $_GET['gn_division'] ?? '';

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

        if ($gnDivision) {
            $baseFrom .= " AND gn.name = :gn_division";
            $params['gn_division'] = $gnDivision;
        }

        $countSql = "SELECT COUNT(*) " . $baseFrom;
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)($countStmt->fetchColumn() ?: 0);

        $sql = "SELECT c.reg_number, c.name, d.name as district, dv.name as division, gn.name as gn_division, et.name as equipment, ce.quantity
                FROM clubs c
                LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
                LEFT JOIN divisions dv ON gn.division_id = dv.id
                LEFT JOIN districts d ON dv.district_id = d.id
                INNER JOIN club_equipment ce ON c.id = ce.club_id
                INNER JOIN equipment_types et ON ce.equipment_type_id = et.id
                WHERE 1=1";

        // Keep select + where identical to $baseFrom, for safety/clarity
        $sql = "SELECT c.reg_number, c.name, d.name as district, dv.name as division, gn.name as gn_division, et.name as equipment, ce.quantity
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

        // Get all divisions for the selected district (or all districts if not selected)
        $divisionSql = "SELECT DISTINCT dv.id, dv.name as division_name
                        FROM divisions dv
                        LEFT JOIN districts d ON dv.district_id = d.id
                        WHERE 1=1";
        $divisionParams = [];

        if ($district) {
            $divisionSql .= " AND d.name = :district";
            $divisionParams['district'] = $district;
        }

        $divisionSql .= " ORDER BY dv.name";
        $divisionStmt = $pdo->prepare($divisionSql);
        $divisionStmt->execute($divisionParams);
        $divisions = $divisionStmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($divisions as $div) {
            $divId = $div['id'];
            $divName = $div['division_name'];

            // Count total registered clubs in this division
            $totalClubsSql = "SELECT COUNT(DISTINCT c.id) as count
                              FROM clubs c
                              LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
                              WHERE gn.division_id = :div_id";
            $totalStmt = $pdo->prepare($totalClubsSql);
            $totalStmt->execute(['div_id' => $divId]);
            $totalResult = $totalStmt->fetch(PDO::FETCH_ASSOC);
            $totalClubs = (int)($totalResult['count'] ?? 0);

            // Count clubs registered in the specific year
            $yearRegisteredSql = "SELECT COUNT(DISTINCT c.id) as count
                                  FROM clubs c
                                  LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
                                  WHERE gn.division_id = :div_id 
                                  AND YEAR(c.registration_date) = :year";
            $yearRegStmt = $pdo->prepare($yearRegisteredSql);
            $yearRegStmt->execute(['div_id' => $divId, 'year' => $year]);
            $yearRegResult = $yearRegStmt->fetch(PDO::FETCH_ASSOC);
            $yearRegistered = (int)($yearRegResult['count'] ?? 0);

            // Count clubs reorganized in the specific year
            $yearReorganizedSql = "SELECT COUNT(DISTINCT c.id) as count
                                   FROM clubs c
                                   LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
                                   INNER JOIN club_reorganizations cr ON c.id = cr.club_id
                                   WHERE gn.division_id = :div_id
                                   AND YEAR(cr.reorg_date) = :year";
            $yearReorgStmt = $pdo->prepare($yearReorganizedSql);
            $yearReorgStmt->execute(['div_id' => $divId, 'year' => $year]);
            $yearReorgResult = $yearReorgStmt->fetch(PDO::FETCH_ASSOC);
            $yearReorganized = (int)($yearReorgResult['count'] ?? 0);

            $data[] = [
                'division_name' => $divName,
                'total_clubs' => $totalClubs,
                'year_registered' => $yearRegistered,
                'year_reorganized' => $yearReorganized
            ];
        }

        $total = count($data);

        // Apply pagination
        if (!$printAll) {
            $data = array_slice($data, $offset, $limit);
        }
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
        // Calculate grand totals from all divisions (before pagination)
        $allDivisionsSql = "SELECT DISTINCT dv.id, dv.name as division_name
                                FROM divisions dv
                                LEFT JOIN districts d ON dv.district_id = d.id
                                WHERE 1=1";
        $allDivsParams = [];

        if ($district) {
            $allDivisionsSql .= " AND d.name = :district";
            $allDivsParams['district'] = $district;
        }

        $allDivsStmt = $pdo->prepare($allDivisionsSql);
        $allDivsStmt->execute($allDivsParams);
        $allDivisions = $allDivsStmt->fetchAll(PDO::FETCH_ASSOC);

        $grandTotalClubs = 0;
        $grandTotalRegistered = 0;
        $grandTotalReorganized = 0;

        foreach ($allDivisions as $div) {
            $divId = $div['id'];

            // Total clubs
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT c.id) as count FROM clubs c LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id WHERE gn.division_id = :div_id");
            $stmt->execute(['div_id' => $divId]);
            $grandTotalClubs += (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

            // Year registered
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT c.id) as count FROM clubs c LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id WHERE gn.division_id = :div_id AND YEAR(c.registration_date) = :year");
            $stmt->execute(['div_id' => $divId, 'year' => $year]);
            $grandTotalRegistered += (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);

            // Year reorganized
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT c.id) as count FROM clubs c LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id INNER JOIN club_reorganizations cr ON c.id = cr.club_id WHERE gn.division_id = :div_id AND YEAR(cr.reorg_date) = :year");
            $stmt->execute(['div_id' => $divId, 'year' => $year]);
            $grandTotalReorganized += (int)($stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0);
        }

        $metadata['pagination']['totals'] = [
            'total_clubs' => $grandTotalClubs,
            'total_registered' => $grandTotalRegistered,
            'total_reorganized' => $grandTotalReorganized
        ];
    }

    sendJSONResponse(true, $data, '', 200, $metadata);
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}
