<?php
// Integration test: ensure summary.php 'byDistrict' matches aggregation from clubs-list.php
// Usage: php tests/integration/test-summary-by-district.php

$base = getenv('BASE_URL') ?: 'http://localhost/sports-v2';

function req($url)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 20,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    return [$code, $body, $err];
}

// 1) Get summary byDistrict
list($code, $body, $err) = req($base . '/api/summary.php');
if ($code >= 500 || $body === false) {
    fwrite(STDERR, "ERROR: summary.php failed ($code) — $err\n");
    exit(2);
}
$summary = json_decode($body, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    fwrite(STDERR, "ERROR: summary.php returned invalid JSON: " . json_last_error_msg() . "\n");
    exit(3);
}
$byDistrict = [];
foreach ($summary['data']['byDistrict'] ?? [] as $r) {
    $byDistrict[$r['district']] = (int)$r['count'];
}

// 2) Get total clubs from clubs-list to determine reasonable limit
list($code, $body, $err) = req($base . '/api/clubs-list.php?limit=1&page=1');
if ($code >= 500 || $body === false) {
    fwrite(STDERR, "ERROR: clubs-list.php failed ($code) — $err\n");
    exit(4);
}
$one = json_decode($body, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    fwrite(STDERR, "ERROR: clubs-list.php returned invalid JSON: " . json_last_error_msg() . "\n");
    exit(5);
}
$total = $one['pagination']['total'] ?? 0;
if ($total <= 0) {
    fwrite(STDOUT, "SKIP: no clubs in DB to validate by-district\n");
    exit(0);
}

// 3) Retrieve all clubs (server supports large limits) and aggregate by district_name
list($code, $body, $err) = req($base . '/api/clubs-list.php?limit=' . $total . '&page=1');
if ($code >= 500 || $body === false) {
    fwrite(STDERR, "ERROR: clubs-list.php failed retrieving all clubs ($code) — $err\n");
    exit(6);
}
$all = json_decode($body, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    fwrite(STDERR, "ERROR: clubs-list.php(all) returned invalid JSON: " . json_last_error_msg() . "\n");
    exit(7);
}
$agg = [];
foreach ($all['data'] as $club) {
    $d = $club['district_name'] ?? '';
    $agg[$d] = ($agg[$d] ?? 0) + 1;
}

// 4) Compare: every district in summary must match aggregated count (allow districts with zero shown in summary)
$errors = [];
foreach ($byDistrict as $district => $count) {
    $expected = $agg[$district] ?? 0;
    if ($expected !== $count) {
        $errors[] = "Mismatch for '{$district}': summary={$count} vs clubs-list={$expected}";
    }
}

if (!empty($errors)) {
    fwrite(STDERR, "FAIL: by-district inconsistencies found:\n" . implode("\n", $errors) . "\n");
    exit(8);
}

fwrite(STDOUT, "OK: byDistrict in summary.php matches clubs-list aggregation\n");
exit(0);
