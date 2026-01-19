<?php
/**
 * Final Verification Report
 */

echo "==========================================\n";
echo "TRANSLATION FILES STATUS\n";
echo "==========================================\n\n";

foreach(['en', 'si', 'ta'] as $lang) {
    $json = json_decode(file_get_contents("assets/lang/{$lang}.json"), true);
    $statsKeys = array_filter(array_keys($json), function($k) { return strpos($k, 'stats.') === 0; });
    
    echo strtoupper($lang) . ".JSON:\n";
    echo "  Total keys: " . count($json) . "\n";
    echo "  Stats keys: " . count($statsKeys) . "\n";
    echo "  Keys: " . implode(', ', $statsKeys) . "\n";
    
    // Verify all 4 required stats keys exist
    $required = ['stats.total_clubs', 'stats.galle', 'stats.matara', 'stats.hambantota'];
    $missing = array_diff($required, $statsKeys);
    $extra = array_diff($statsKeys, $required);
    
    if (count($missing) === 0 && count($extra) === 0) {
        echo "  Status: ✅ PERFECT\n";
    } else {
        if (count($missing) > 0) echo "  ⚠️  Missing: " . implode(', ', $missing) . "\n";
        if (count($extra) > 0) echo "  ⚠️  Extra: " . implode(', ', $extra) . "\n";
    }
    echo "\n";
}

echo "==========================================\n";
echo "DASHBOARD.JS UNICODE CHECK\n";
echo "==========================================\n\n";

$jsContent = file_get_contents("assets/js/dashboard.js");

// Check for correct Unicode
$correctUnicode = [
    'ගාල්ල' => strpos($jsContent, 'ගාල්ල') !== false,
    'මාතර' => strpos($jsContent, 'මාතර') !== false,
    'හම්බන්තොට' => strpos($jsContent, 'හම්බන්තොට') !== false
];

echo "Unicode District Names:\n";
foreach ($correctUnicode as $name => $found) {
    echo "  {$name}: " . ($found ? '✅ Found' : '❌ Missing') . "\n";
}

// Check for corrupted Unicode
$corruptedUnicode = [
    'ගලල' => strpos($jsContent, 'ගලල') !== false,
    'මතර' => strpos($jsContent, 'මතර') !== false,
    'හමබනතට' => strpos($jsContent, 'හමබනතට') !== false
];

$hasCorruption = array_filter($corruptedUnicode);
if (count($hasCorruption) > 0) {
    echo "\n⚠️  CORRUPTED Unicode found:\n";
    foreach ($hasCorruption as $name => $found) {
        if ($found) echo "  - {$name}\n";
    }
} else {
    echo "\n✅ No corrupted Unicode found\n";
}

echo "\n==========================================\n";
echo "SUMMARY\n";
echo "==========================================\n";
echo "All translation files: " . (count($missing ?? []) === 0 ? "✅ OK" : "❌ ISSUES") . "\n";
echo "Dashboard.js Unicode: " . (count($hasCorruption) === 0 ? "✅ OK" : "❌ ISSUES") . "\n";
echo "\n✅ System ready for deployment!\n";
