<?php
// Quick integration test to ensure summary SSE emits a 'data:' event
// Usage: php tests/integration/test-summary-sse.php

$url = getenv('BASE_URL') ?: 'http://localhost/sports-v2/api/summary-stream.php';

// Open a streaming HTTP connection and wait up to 6s for either a 'data:' event
$ctx = stream_context_create(['http' => ['method' => 'GET', 'timeout' => 6]]);
$fp = @fopen($url, 'r', false, $ctx);
if (!$fp) {
    fwrite(STDERR, "ERROR: could not open stream to $url\n");
    exit(2);
}

// set non-blocking and wait for data with a short timeout
stream_set_blocking($fp, false);
$start = time();
$buffer = '';
$timeout = 6;
while ((time() - $start) < $timeout) {
    $chunk = fread($fp, 8192);
    if ($chunk !== false && $chunk !== '') {
        $buffer .= $chunk;
        // accept either a data: JSON line or a SSE comment (heartbeat)
        if (preg_match('/^data:\s*(\{.*\})/m', $buffer, $m)) {
            $json = $m[1];
            $data = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                fwrite(STDOUT, "OK: SSE emitted 'data:' with valid JSON\n");
                fclose($fp);
                exit(0);
            }
            fwrite(STDERR, "FAIL: data payload not valid JSON\n");
            fclose($fp);
            exit(3);
        }
        if (preg_match('/^:\s*ping/m', $buffer)) {
            fwrite(STDOUT, "OK: SSE heartbeat received\n");
            fclose($fp);
            exit(0);
        }
    }
    usleep(100000); // 100ms
}

fclose($fp);
fwrite(STDERR, "FAIL: no SSE data/heartbeat received within {$timeout}s (got: " . substr($buffer,0,1000) . ")\n");
exit(4);
