<?php
// Simple integration test for api/clubs-list.php
// Usage: php tests/integration/test-clubs-list.php

$url = getenv('BASE_URL') ?: 'http://localhost/sports-v2/api/clubs-list.php?district_id=1&page=1&limit=1';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 10,
]);

$body = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($body === false || $httpCode >= 500) {
    fwrite(STDERR, "ERROR: HTTP $httpCode returned from $url\n");
    if ($err) fwrite(STDERR, "cURL error: $err\n");
    exit(2);
}

$data = json_decode($body, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    fwrite(STDERR, "ERROR: response is not valid JSON (" . json_last_error_msg() . ")\n");
    fwrite(STDERR, "BODY:\n" . substr($body, 0, 400) . "\n");
    exit(3);
}

// Basic shape assertions
if (!isset($data['success']) || $data['success'] !== true) {
    fwrite(STDERR, "FAIL: success !== true\n");
    fwrite(STDERR, "RESPONSE: " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n");
    exit(4);
}
if (!isset($data['data']) || !is_array($data['data'])) {
    fwrite(STDERR, "FAIL: data missing or not an array\n");
    exit(5);
}
if (!isset($data['pagination']) || !isset($data['pagination']['total'])) {
    fwrite(STDERR, "WARN: pagination missing (non-fatal)\n");
}

fwrite(STDOUT, "OK: api/clubs-list.php responded with success and an array of clubs (showing up to 1)\n");
exit(0);
