<?php

/**
 * Club Reorganization API
 * Handles club reorganization with full history tracking
 */

// Set error handling to prevent HTML output
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Enable output buffering to catch any stray output
ob_start();

require_once '../config/database.php';
header('Content-Type: application/json; charset=utf-8');

// Initialize database connection
$pdo = null;
try {
    $pdo = getDBConnection();
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    ob_end_flush();
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            reorganizeClub();
            break;
        case 'GET':
            getReorganizationHistory();
            break;
        case 'DELETE':
            deleteReorganization();
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    // Clear any output that might have been buffered
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

// Output the buffered content
ob_end_flush();

/**
 * Reorganize a club (update information and track history)
 */
function reorganizeClub()
{
    global $pdo;

    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    $required = [
        'club_id',
        'reorg_date',
        'name',
        'chairman_name',
        'chairman_address',
        'chairman_phone',
        'secretary_name',
        'secretary_address',
        'secretary_phone'
    ];

    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            return;
        }
    }

    try {
        $pdo->beginTransaction();

        // Get current club information
        $stmt = $pdo->prepare("
            SELECT name, chairman_name, chairman_address, chairman_phone,
                   secretary_name, secretary_address, secretary_phone, gn_division_id
            FROM clubs WHERE id = ?
        ");
        $stmt->execute([$data['club_id']]);
        $currentClub = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentClub) {
            throw new Exception('Club not found');
        }

        // Insert reorganization history (only previous values, no location)
        $stmt = $pdo->prepare("
            INSERT INTO club_reorganizations (
                club_id, reorg_date,
                prev_name, prev_chairman_name, prev_chairman_address, prev_chairman_phone,
                prev_secretary_name, prev_secretary_address, prev_secretary_phone,
                notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['club_id'],
            $data['reorg_date'],
            $currentClub['name'],
            $currentClub['chairman_name'],
            $currentClub['chairman_address'],
            $currentClub['chairman_phone'],
            $currentClub['secretary_name'],
            $currentClub['secretary_address'],
            $currentClub['secretary_phone'],
            $data['notes'] ?? null
        ]);

        // Update club with new information
        $stmt = $pdo->prepare("
            UPDATE clubs SET
                name = ?,
                chairman_name = ?,
                chairman_address = ?,
                chairman_phone = ?,
                secretary_name = ?,
                secretary_address = ?,
                secretary_phone = ?,
                gn_division_id = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $data['name'],
            $data['chairman_name'],
            $data['chairman_address'],
            $data['chairman_phone'],
            $data['secretary_name'],
            $data['secretary_address'],
            $data['secretary_phone'],
            $data['gs_division_id'] ?? null,
            $data['club_id']
        ]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Club reorganized successfully'
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/**
 * Delete a reorganization record
 */
function deleteReorganization()
{
    global $pdo;

    $data = json_decode(file_get_contents('php://input'), true);
    $reorgId = $data['id'] ?? null;

    if (!$reorgId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Reorganization ID required']);
        return;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM club_reorganizations WHERE id = ?");
        $stmt->execute([$reorgId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Reorganization deleted successfully'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Reorganization not found']);
        }
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Get reorganization history for a club
 */
function getReorganizationHistory()
{
    global $pdo;

    $clubId = $_GET['club_id'] ?? null;

    if (!$clubId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Club ID required']);
        return;
    }

    try {
        // Check if table exists first
        $stmt = $pdo->query("SHOW TABLES LIKE 'club_reorganizations'");
        if (!$stmt->fetch()) {
            echo json_encode([
                'success' => true,
                'data' => [],
                'message' => 'Reorganization table not created yet. Please run the migration SQL.'
            ]);
            return;
        }

        $stmt = $pdo->prepare("
            SELECT 
                r.*,
                c.name as current_name,
                c.chairman_name as current_chairman_name,
                c.chairman_address as current_chairman_address,
                c.chairman_phone as current_chairman_phone,
                c.secretary_name as current_secretary_name,
                c.secretary_address as current_secretary_address,
                c.secretary_phone as current_secretary_phone
            FROM club_reorganizations r
            INNER JOIN clubs c ON r.club_id = c.id
            WHERE r.club_id = ?
            ORDER BY r.reorg_date DESC
        ");

        $stmt->execute([$clubId]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'data' => $history
        ]);
    } catch (Exception $e) {
        // Table might not exist yet
        echo json_encode([
            'success' => true,
            'data' => []
        ]);
    }
}