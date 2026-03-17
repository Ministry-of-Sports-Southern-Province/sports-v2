<?php

/**
 * Public Clubs API
 * No authentication required. Returns club list with no personal information
 * (no chairman/secretary names, addresses, or phone numbers).
 */

header('Content-Type: application/json; charset=UTF-8');
require_once '../config/database.php';

// ------------------------------------------------------------------
// Stats mode — lightweight count summary for the dashboard cards
// ------------------------------------------------------------------
if (isset($_GET['stats']) && $_GET['stats'] === '1') {
    try {
        $pdo = getDBConnection();

        $total = (int)$pdo->query("SELECT COUNT(*) FROM clubs")->fetchColumn();

        $activeHaving = "HAVING (CASE WHEN MAX(cr.reorg_date) IS NULL THEN 0"
            . " WHEN MONTH(MAX(cr.reorg_date)) > 6 THEN (CURDATE() < CONCAT(YEAR(MAX(cr.reorg_date))+2,'-01-01'))"
            . " ELSE (CURDATE() < DATE_ADD(MAX(cr.reorg_date), INTERVAL 1 YEAR)) END) = 1";

        $active = (int)$pdo->query(
            "SELECT COUNT(*) FROM (
                SELECT c.id FROM clubs c
                LEFT JOIN club_reorganizations cr ON c.id = cr.club_id
                GROUP BY c.id {$activeHaving}
            ) AS sub"
        )->fetchColumn();

        sendJSONResponse(true, ['total' => $total, 'active' => $active, 'expired' => $total - $active]);
    } catch (Exception $e) {
        sendJSONResponse(false, null, $e->getMessage(), 500);
    }
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendJSONResponse(false, null, 'Invalid request method', 405);
    }

    $pdo = getDBConnection();

    // ------------------------------------------------------------------
    // Parameters
    // ------------------------------------------------------------------
    $search     = trim($_GET['search'] ?? '');
    $districtId = isset($_GET['district_id']) ? (int)$_GET['district_id'] : null;
    $divisionId = isset($_GET['division_id']) ? (int)$_GET['division_id'] : null;

    $reorgStatus = isset($_GET['reorg_status']) ? trim($_GET['reorg_status']) : null;
    if ($reorgStatus !== 'active' && $reorgStatus !== 'expired') {
        $reorgStatus = null;
    }

    $page  = max(1, (int)($_GET['page'] ?? 1));
    $limit = (int)($_GET['limit'] ?? 20);
    if ($limit < 1)   $limit = 20;
    if ($limit > 200) $limit = 200;

    // ------------------------------------------------------------------
    // Base query  (no personal columns)
    // ------------------------------------------------------------------
    $baseFrom = "FROM clubs c
            LEFT JOIN grama_niladhari_divisions gn ON c.gn_division_id = gn.id
            LEFT JOIN divisions dv ON gn.division_id = dv.id
            LEFT JOIN districts d  ON dv.district_id = d.id
            LEFT JOIN club_reorganizations cr ON c.id = cr.club_id
            WHERE 1=1";

    $where  = '';
    $params = [];

    // Search by club name or registration number only (never chairman/secretary)
    if ($search !== '') {
        $where .= " AND (c.name LIKE :search_name OR c.reg_number LIKE :search_reg)";
        $params['search_name'] = '%' . $search . '%';
        $params['search_reg']  = '%' . $search . '%';
    }

    if ($districtId) {
        $where .= " AND d.id = :district_id";
        $params['district_id'] = $districtId;
    }

    if ($divisionId) {
        $where .= " AND dv.id = :division_id";
        $params['division_id'] = $divisionId;
    }

    // Reorganization status filter uses HAVING (same logic as clubs-list.php)
    $having = '';
    if ($reorgStatus === 'active') {
        $having = " HAVING (CASE WHEN MAX(cr.reorg_date) IS NULL THEN 0"
            . " WHEN MONTH(MAX(cr.reorg_date)) > 6 THEN (CURDATE() < CONCAT(YEAR(MAX(cr.reorg_date))+2, '-01-01'))"
            . " ELSE (CURDATE() < DATE_ADD(MAX(cr.reorg_date), INTERVAL 1 YEAR)) END) = 1";
    } elseif ($reorgStatus === 'expired') {
        $having = " HAVING (CASE WHEN MAX(cr.reorg_date) IS NULL THEN 1"
            . " WHEN MONTH(MAX(cr.reorg_date)) > 6 THEN (CURDATE() >= CONCAT(YEAR(MAX(cr.reorg_date))+2, '-01-01'))"
            . " ELSE (CURDATE() >= DATE_ADD(MAX(cr.reorg_date), INTERVAL 1 YEAR)) END) = 1";
    }

    // ------------------------------------------------------------------
    // Count
    // ------------------------------------------------------------------
    if ($having !== '') {
        $countSql = "SELECT COUNT(*) as total FROM (SELECT c.id " . $baseFrom . $where . " GROUP BY c.id" . $having . ") AS sub";
    } else {
        $countSql = "SELECT COUNT(DISTINCT c.id) as total " . $baseFrom . $where;
    }

    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total      = (int)($countStmt->fetchColumn() ?: 0);
    $totalPages = max(1, (int)ceil($total / $limit));
    if ($page > $totalPages) $page = $totalPages;
    $offset = ($page - 1) * $limit;

    // ------------------------------------------------------------------
    // Data — strictly no personal columns
    // ------------------------------------------------------------------
    $sql = "SELECT
                c.id,
                c.reg_number,
                c.name,
                c.registration_date,
                d.name  AS district_name,
                dv.name AS division_name,
                gn.name AS gs_division_name,
                MAX(cr.reorg_date) AS last_reorg_date
            " . $baseFrom . $where . "
            GROUP BY c.id, c.reg_number, c.name, c.registration_date, d.name, dv.name, gn.name"
        . $having . "
            ORDER BY c.registration_date DESC, c.id DESC
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $val) {
        $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue(':' . $key, $val, $type);
    }
    $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ------------------------------------------------------------------
    // Calculate reorg status for each club
    // ------------------------------------------------------------------
    foreach ($clubs as &$club) {
        if ($club['last_reorg_date']) {
            $dt    = new DateTime($club['last_reorg_date']);
            $month = (int)$dt->format('n');
            $year  = (int)$dt->format('Y');

            $club['reorg_due_date'] = ($month > 6)
                ? ($year + 2) . '-01-01'
                : date('Y-m-d', strtotime($club['last_reorg_date'] . ' +1 year'));

            $club['reorg_status'] = ($club['reorg_due_date'] <= date('Y-m-d')) ? 'expired' : 'active';
        } else {
            $club['reorg_due_date'] = null;
            $club['reorg_status']   = 'expired';
        }
    }
    unset($club);

    sendJSONResponse(true, $clubs, 'Clubs loaded successfully', 200, [
        'pagination' => [
            'page'        => $page,
            'limit'       => $limit,
            'total'       => $total,
            'total_pages' => $totalPages,
        ]
    ]);
} catch (Exception $e) {
    sendJSONResponse(false, null, $e->getMessage(), 500);
}
