<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';

try {
    $pdo = getDBConnection();
    $type = $_GET['type'] ?? 'registered';
    $columns = explode(',', $_GET['columns'] ?? 'reg_number,name,district');

    $data = [];

    if ($type === 'reorganized') {
        $year = $_GET['year'] ?? date('Y');
        $district = $_GET['district'] ?? '';

        $sql = "SELECT c.reg_number, c.name, c.chairman_name as chairman, c.chairman_phone, 
                       c.secretary_name as secretary, c.registration_date, d.name as district, 
                       dv.name as division, cr.reorg_date
                FROM clubs c
                LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
                LEFT JOIN divisions dv ON gn.division_id = dv.id
                LEFT JOIN districts d ON dv.district_id = d.id
                INNER JOIN club_reorganizations cr ON c.id = cr.club_id
                WHERE YEAR(cr.reorg_date) = :year";

        $params = ['year' => $year];

        if ($district) {
            $sql .= " AND d.name = :district";
            $params['district'] = $district;
        }

        $sql .= " ORDER BY cr.reorg_date DESC, c.name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($type === 'registered') {
        $district = $_GET['district'] ?? '';
        $dateRange = $_GET['date_range'] ?? 'all';

        $sql = "SELECT c.reg_number, c.name, c.chairman_name as chairman, c.chairman_phone, 
                       c.secretary_name as secretary, c.registration_date, d.name as district, 
                       dv.name as division
                FROM clubs c
                LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
                LEFT JOIN divisions dv ON gn.division_id = dv.id
                LEFT JOIN districts d ON dv.district_id = d.id
                WHERE 1=1";

        $params = [];

        if ($district) {
            $sql .= " AND d.name = :district";
            $params['district'] = $district;
        }

        if ($dateRange === 'year') {
            $sql .= " AND YEAR(c.registration_date) = YEAR(CURDATE())";
        } elseif ($dateRange === 'month') {
            $sql .= " AND YEAR(c.registration_date) = YEAR(CURDATE()) AND MONTH(c.registration_date) = MONTH(CURDATE())";
        }

        $sql .= " ORDER BY c.registration_date DESC, c.name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($type === 'equipment') {
        $equipment = $_GET['equipment'] ?? '';

        $sql = "SELECT c.reg_number, c.name, d.name as district, et.name as equipment, ce.quantity
                FROM clubs c
                LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
                LEFT JOIN divisions dv ON gn.division_id = dv.id
                LEFT JOIN districts d ON dv.district_id = d.id
                INNER JOIN club_equipment ce ON c.id = ce.club_id
                INNER JOIN equipment_types et ON ce.equipment_type_id = et.id
                WHERE 1=1";

        $params = [];

        if ($equipment) {
            $sql .= " AND et.name = :equipment";
            $params['equipment'] = $equipment;
        }

        $sql .= " ORDER BY d.name, c.name, et.name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    sendJSONResponse(true, $data);
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}
