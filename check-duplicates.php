<?php
// Check for duplicates in all translation files
foreach(['en', 'si', 'ta'] as $lang) {
    $json = json_decode(file_get_contents("assets/lang/{$lang}.json"), true);
    $keys = array_keys($json);
    $dupes = array_filter(array_count_values($keys), function($v) { return $v > 1; });
    
    echo "{$lang}.json: " . count($json) . " keys, " . count($dupes) . " duplicates\n";
    if (count($dupes) > 0) {
        print_r($dupes);
    }
    
    // Check for stats keys specifically
    $statsKeys = array_filter($keys, function($k) { return strpos($k, 'stats.') === 0; });
    echo "  Stats keys: " . count($statsKeys) . " - " . implode(', ', $statsKeys) . "\n\n";
}
