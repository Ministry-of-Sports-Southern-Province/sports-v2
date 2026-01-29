<?php
// Integration test for unicode search on api/clubs-list.php
// Usage: php tests/integration/test-clubs-list-search-unicode.php

$term = urlencode('ද්'); // common Sinhala character used in reported bug
$url = (getenv('BASE_URL') ?: 'http://localhost/sports-v2') . "/api/clubs-list.php?search={$term}&page=1&limit=5";

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
    fwrite(STDERR, "BODY:\n" . substr($body, 0, 800) . "\n");
    exit(3);
}

if (!isset($data['success']) || $data['success'] !== true) {
    fwrite(STDERR, "FAIL: success !== true\n");
    fwrite(STDERR, "RESPONSE: " . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n");
    exit(4);
}

if (!isset($data['data']) || !is_array($data['data'])) {
    fwrite(STDERR, "FAIL: data missing or not an array\n");
    exit(5);
}

fwrite(STDOUT, "OK: unicode search returned HTTP $httpCode and a valid JSON payload (data array present)\n");
exit(0);
