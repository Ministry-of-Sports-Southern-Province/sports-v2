<?php

/**
 * Clubs List API
 * Returns list of clubs with filtering and search
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
    $gsDivisionId = $_GET['gs_division_id'] ?? null;
    $reorgStatus = isset($_GET['reorg_status']) ? trim($_GET['reorg_status']) : null;
    if ($reorgStatus !== null && $reorgStatus !== 'active' && $reorgStatus !== 'expired') {
        $reorgStatus = null;
    }

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $printAll = isset($_GET['print_all']) && (string)$_GET['print_all'] === '1';
    $maxPrintAll = 2000;

    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 10;
    if ($limit > 200) $limit = 200;
    $offset = ($page - 1) * $limit;

    // Build query
    $baseFrom = "FROM clubs c
            LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
            LEFT JOIN divisions dv ON gn.division_id = dv.id
            LEFT JOIN districts d ON dv.district_id = d.id
            LEFT JOIN club_reorganizations cr ON c.id = cr.club_id
            WHERE 1=1";

    $where = "";
    $params = [];

    // Add search filter
    // NOTE: PDO native prepares (emulate = false) do not support re-using the same named
    // parameter multiple times in a single statement. Use distinct names and bind the
    // same value to each to remain compatible with ATTR_EMULATE_PREPARES = false.
    if ($search !== '') {
        $where .= " AND (c.name LIKE :search_name OR c.reg_number LIKE :search_reg OR c.chairman_name LIKE :search_chair)";
        $params['search_name'] = '%' . $search . '%';
        $params['search_reg'] = '%' . $search . '%';
        $params['search_chair'] = '%' . $search . '%';
    }

    // Add district filter
    if ($districtId) {
        $where .= " AND d.id = :district_id";
        $params['district_id'] = (int)$districtId;
    }

    // Add division filter
    if ($divisionId) {
        $where .= " AND dv.id = :division_id";
        $params['division_id'] = (int)$divisionId;
    }

    // Add GN division filter
    if ($gsDivisionId) {
        $where .= " AND gn.id = :gs_division_id";
        $params['gs_division_id'] = (int)$gsDivisionId;
    }

    // HAVING for reorg_status (active = due date in future, expired = due date past or no reorg)
    $having = '';
    if ($reorgStatus === 'active') {
        $having = " HAVING (CASE WHEN MAX(cr.reorg_date) IS NULL THEN 0 WHEN MONTH(MAX(cr.reorg_date)) > 6 THEN (CURDATE() < CONCAT(YEAR(MAX(cr.reorg_date))+2, '-01-01')) ELSE (CURDATE() < DATE_ADD(MAX(cr.reorg_date), INTERVAL 1 YEAR)) END) = 1";
    } elseif ($reorgStatus === 'expired') {
        $having = " HAVING (CASE WHEN MAX(cr.reorg_date) IS NULL THEN 1 WHEN MONTH(MAX(cr.reorg_date)) > 6 THEN (CURDATE() >= CONCAT(YEAR(MAX(cr.reorg_date))+2, '-01-01')) ELSE (CURDATE() >= DATE_ADD(MAX(cr.reorg_date), INTERVAL 1 YEAR)) END) = 1";
    }

    // Total count (distinct clubs) for pagination
    if ($having !== '') {
        $countSql = "SELECT COUNT(*) as total FROM (SELECT c.id " . $baseFrom . $where . " GROUP BY c.id" . $having . ") AS sub";
    } else {
        $countSql = "SELECT COUNT(DISTINCT c.id) as total " . $baseFrom . $where;
    }
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)($countStmt->fetchColumn() ?: 0);
    if ($printAll && $total > $maxPrintAll) {
        sendJSONResponse(false, null, 'Too many rows to print all. Please apply filters.', 400);
    }
    $totalPages = $limit > 0 ? (int)ceil($total / $limit) : 1;
    if ($totalPages < 1) $totalPages = 1;
    if ($page > $totalPages) $page = $totalPages;
    $offset = ($page - 1) * $limit;

    $sql = "SELECT 
                c.id,
                c.reg_number,
                c.name,
                c.registration_date,
                c.date_entry_type,
                c.chairman_name,
                c.chairman_address,
                c.chairman_phone,
                c.secretary_name,
                c.secretary_address,
                c.secretary_phone,
                d.name as district_name,
                d.sinhala_letter as district_letter,
                dv.name as division_name,
                gn.name as gs_division_name,
                MAX(cr.reorg_date) as last_reorg_date
            " . $baseFrom . $where . "
            GROUP BY c.id" . $having . "
            ORDER BY c.registration_date DESC, c.created_at DESC";

    if (!$printAll) {
        $sql .= " LIMIT :limit OFFSET :offset";
    }

    $stmt = $pdo->prepare($sql);
    // Bind named parameters (preserve integer types when possible)
    foreach ($params as $name => $val) {
        $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue(':' . $name, $val, $type);
    }
    if (!$printAll) {
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    }
    $stmt->execute();
    $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate due date and status for each club
    foreach ($clubs as &$club) {
        if ($club['last_reorg_date']) {
            $reorgDateTime = new DateTime($club['last_reorg_date']);
            $reorgMonth = (int)$reorgDateTime->format('n');
            $reorgYear = (int)$reorgDateTime->format('Y');

            if ($reorgMonth > 6) {
                $club['reorg_due_date'] = ($reorgYear + 2) . '-01-01';
            } else {
                $club['reorg_due_date'] = date('Y-m-d', strtotime($club['last_reorg_date'] . ' +1 year'));
            }

            $club['reorg_status'] = ($club['reorg_due_date'] <= date('Y-m-d')) ? 'expired' : 'active';
        } else {
            $club['reorg_due_date'] = null;
            $club['reorg_status'] = 'expired';
        }

        // Backward-/frontend-friendly alias
        $club['next_reorg_due_date'] = $club['reorg_due_date'];
    }

    $pagination = [
        'page' => $printAll ? 1 : $page,
        'limit' => $printAll ? $total : $limit,
        'total' => $total,
        'total_pages' => $printAll ? 1 : $totalPages
    ];

    sendJSONResponse(true, $clubs, 'Clubs loaded successfully', 200, [
        'pagination' => $pagination
    ]);
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}
