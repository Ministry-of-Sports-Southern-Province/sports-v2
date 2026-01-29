<?php
// Server-Sent Events endpoint for realtime summary updates
// Clients: EventSource -> /api/summary-stream.php

require_once '../config/database.php';

// Keep the connection alive
@set_time_limit(0);
ignore_user_abort(true);

header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Helper to send an SSE event
function sse_send($event, $data)
{
    echo "event: {$event}\n";
    // each line of data must be prefixed with "data: "
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    $lines = preg_split("/\r?\n/", $json);
    foreach ($lines as $line) {
        echo "data: {$line}\n";
    }
    echo "\n";
    @ob_flush();
    @flush();
}

function load_summary_snapshot(PDO $pdo)
{
    // Duplicate of api/summary.php's queries (kept compact to avoid include issues)
    $out = [
        'total' => 0,
        'active' => 0,
        'expired' => 0,
        'totalReorgs' => 0,
        'byDistrict' => [],
        'byStatus' => ['active' => 0, 'expired' => 0],
        'registrationTrend' => []
    ];

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM clubs");
    $out['total'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

    try {
        $stmt = $pdo->query("SELECT 
            SUM(CASE WHEN last_reorg_date >= DATE_SUB(CURDATE(), INTERVAL 2 YEAR) THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN last_reorg_date < DATE_SUB(CURDATE(), INTERVAL 2 YEAR) OR last_reorg_date IS NULL THEN 1 ELSE 0 END) as expired
            FROM clubs");
        $status = $stmt->fetch(PDO::FETCH_ASSOC);
        $out['byStatus'] = ['active' => (int)$status['active'], 'expired' => (int)$status['expired']];
        $out['active'] = (int)$status['active'];
        $out['expired'] = (int)$status['expired'];
    } catch (Exception $e) {
        $out['active'] = $out['total'];
        $out['expired'] = 0;
    }

    try {
        $stmt = $pdo->query("SELECT d.name as district, COUNT(c.id) as count 
            FROM districts d 
            LEFT JOIN clubs c ON c.district_id = d.id 
            GROUP BY d.id, d.name 
            ORDER BY d.name");
        $out['byDistrict'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $out['byDistrict'] = [];
    }

    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM club_reorganizations");
        $out['totalReorgs'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    } catch (Exception $e) {
        $out['totalReorgs'] = 0;
    }

    try {
        $stmt = $pdo->query("SELECT 
            DATE_FORMAT(registration_date, '%Y-%m') as month,
            COUNT(*) as count
            FROM clubs
            WHERE registration_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(registration_date, '%Y-%m')
            ORDER BY month");
        $out['registrationTrend'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $out['registrationTrend'] = [];
    }

    return $out;
}

try {
    $pdo = getDBConnection();
} catch (Exception $e) {
    // can't open DB — send a single error event and exit
    sse_send('error', ['message' => 'database connection failed']);
    exit;
}

$lastHash = null;
$heartbeatInterval = 15; // seconds
$pollInterval = 3; // seconds (how often we check for changes)
$elapsedSinceHeartbeat = 0;

while (!connection_aborted()) {
    $snapshot = load_summary_snapshot($pdo);
    $hash = md5(json_encode($snapshot, JSON_UNESCAPED_UNICODE));

    if ($hash !== $lastHash) {
        sse_send('message', ['data' => $snapshot]);
        $lastHash = $hash;
        $elapsedSinceHeartbeat = 0;
    } else {
        // send heartbeat comment periodically so proxies don't kill the connection
        if ($elapsedSinceHeartbeat >= $heartbeatInterval) {
            echo ": ping\n\n";
            @ob_flush();
            @flush();
            $elapsedSinceHeartbeat = 0;
        }
    }

    // sleep in small increments so we can break quickly on connection close
    $sleep = $pollInterval;
    while ($sleep > 0 && !connection_aborted()) {
        usleep(200000); // 200ms
        $sleep -= 0.2;
        $elapsedSinceHeartbeat += 0.2;
    }
}

// clean close
exit;
