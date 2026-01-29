<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';

try {
    $pdo = getDBConnection();
    $type = $_GET['type'] ?? 'registered';
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
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $totalPages = $limit > 0 ? (int)ceil($total / $limit) : 1;
    if ($totalPages < 1) $totalPages = 1;
    if ($page > $totalPages) $page = $totalPages;

    $pagination = [
        'page' => $printAll ? 1 : $page,
        'limit' => $printAll ? $total : $limit,
        'total' => $total,
        'total_pages' => $printAll ? 1 : $totalPages
    ];

    sendJSONResponse(true, $data, '', 200, [
        'pagination' => $pagination
    ]);
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}
