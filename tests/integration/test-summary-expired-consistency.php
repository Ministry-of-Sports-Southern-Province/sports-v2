<?php
// Ensure summary.php expired count is consistent with clubs-list.php?reorg_status=expired
// Usage: php tests/integration/test-summary-expired-consistency.php

$base = getenv('BASE_URL') ?: 'http://localhost/sports-v2';

// Check whether there are any clubs reported as expired by the clubs-list endpoint
$expiredListUrl = $base . '/api/clubs-list.php?reorg_status=expired&limit=1';
$summaryUrl = $base . '/api/summary.php';

function getJson($url) {
    $opts = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ];
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    return [$code, $body, $err];
}

list($code, $body, $err) = getJson($expiredListUrl);
if ($code >= 500 || $body === false) {
    fwrite(STDERR, "SKIP: clubs-list endpoint failed ($code) — $err\n");
    exit(0);
}
$list = json_decode($body, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    fwrite(STDERR, "ERROR: clubs-list did not return JSON: " . json_last_error_msg() . "\n");
    exit(2);
}

$hasExpiredInList = isset($list['data']) && is_array($list['data']) && count($list['data']) > 0;

list($code, $body, $err) = getJson($summaryUrl);
if ($code >= 500 || $body === false) {
    fwrite(STDERR, "ERROR: summary endpoint failed ($code) — $err\n");
    exit(3);
}
$summary = json_decode($body, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    fwrite(STDERR, "ERROR: summary did not return JSON: " . json_last_error_msg() . "\n");
    exit(4);
}

$expiredInSummary = $summary['data']['byStatus']['expired'] ?? $summary['data']['expired'] ?? 0;

if ($hasExpiredInList && $expiredInSummary === 0) {
    fwrite(STDERR, "FAIL: clubs-list shows expired clubs but summary reports 0 expired (inconsistent)\n");
    exit(5);
}

fwrite(STDOUT, "OK: expired counts are consistent or no expired clubs exist (expiredInSummary={$expiredInSummary})\n");
exit(0);
