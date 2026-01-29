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
    $gnDivisionId = $_GET['gn_division_id'] ?? null;

    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $printAll = isset($_GET['print_all']) && (string)$_GET['print_all'] === '1';

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
    if ($search !== '') {
        $where .= " AND (c.name LIKE ? OR c.reg_number LIKE ? OR c.chairman_name LIKE ?)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    // Add district filter
    if ($districtId) {
        $where .= " AND d.id = ?";
        $params[] = $districtId;
    }

    // Add division filter
    if ($divisionId) {
        $where .= " AND dv.id = ?";
        $params[] = $divisionId;
    }

    // Add GN division filter
    if ($gnDivisionId) {
        $where .= " AND gn.id = ?";
        $params[] = $gnDivisionId;
    }

    // Total count (distinct clubs) for pagination
    $countSql = "SELECT COUNT(DISTINCT c.id) as total " . $baseFrom . $where;
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = (int)($countStmt->fetchColumn() ?: 0);
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
                d.name as district_name,
                dv.name as division_name,
                gn.name as gn_division_name,
                MAX(cr.reorg_date) as last_reorg_date
            " . $baseFrom . $where . "
            GROUP BY c.id
            ORDER BY c.registration_date DESC, c.created_at DESC";

    if (!$printAll) {
        $sql .= " LIMIT :limit OFFSET :offset";
    }

    $stmt = $pdo->prepare($sql);
    foreach ($params as $idx => $val) {
        $stmt->bindValue($idx + 1, $val);
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
