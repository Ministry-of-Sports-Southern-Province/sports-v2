<?php
/**
 * Fix all Unicode and duplicate issues
 */

// 1. Fix dashboard.js Unicode issues
$jsFile = __DIR__ . '/assets/js/dashboard.js';
$jsContent = file_get_contents($jsFile);

// Replace corrupted Sinhala with correct Unicode
$jsContent = str_replace("districtName.includes('ගලල')", "districtName.includes('ගාල්ල')", $jsContent);
$jsContent = str_replace("districtName.includes('මතර')", "districtName.includes('මාතර')", $jsContent);
$jsContent = str_replace("districtName.includes('හමබනතට')", "districtName.includes('හම්බන්තොට')", $jsContent);

file_put_contents($jsFile, $jsContent);
echo "✓ Fixed Unicode in dashboard.js\n";

// 2. Remove extra stats keys from si.json and ta.json
foreach(['si', 'ta'] as $lang) {
    $file = __DIR__ . "/assets/lang/{$lang}.json";
    $json = json_decode(file_get_contents($file), true);
    
    // Remove the extra stats keys that aren't in English
    unset($json['stats.districts']);
    unset($json['stats.clubs']);
    unset($json['stats.availability']);
    
    file_put_contents($file, json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    echo "✓ Removed extra stats keys from {$lang}.json (now " . count($json) . " keys)\n";
}

echo "\n✅ All Unicode issues fixed and duplicate stats removed!\n";
