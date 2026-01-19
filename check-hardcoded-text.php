<?php
echo "Checking for hardcoded Sinhala/Tamil in dashboard.js...\n\n";

$content = file_get_contents('assets/js/dashboard.js');

// Check for Sinhala Unicode range (U+0D80 to U+0DFF)
$sinhalaPattern = '/[\x{0D80}-\x{0DFF}]/u';
$sinhalaMatches = [];
preg_match_all($sinhalaPattern, $content, $sinhalaMatches);

// Check for Tamil Unicode range (U+0B80 to U+0BFF)
$tamilPattern = '/[\x{0B80}-\x{0BFF}]/u';
$tamilMatches = [];
preg_match_all($tamilPattern, $content, $tamilMatches);

if (count($sinhalaMatches[0]) > 0) {
    echo "❌ Found " . count($sinhalaMatches[0]) . " Sinhala characters\n";
    echo "Sample: " . implode('', array_unique(array_slice($sinhalaMatches[0], 0, 10))) . "\n\n";
} else {
    echo "✅ No hardcoded Sinhala text\n";
}

if (count($tamilMatches[0]) > 0) {
    echo "❌ Found " . count($tamilMatches[0]) . " Tamil characters\n";
    echo "Sample: " . implode('', array_unique(array_slice($tamilMatches[0], 0, 10))) . "\n\n";
} else {
    echo "✅ No hardcoded Tamil text\n";
}

// Check specific patterns that should use English defaults
$patterns = [
    'data-i18n="message.loading">' => 'Loading...',
    'data-i18n="table.no_data">' => 'No data available',
    'data-i18n="button.view_details">' => 'View Details'
];

echo "\n--- Checking default text patterns ---\n";
foreach ($patterns as $pattern => $expected) {
    if (preg_match('/' . preg_quote($pattern, '/') . '([^<]+)/', $content, $matches)) {
        $actual = trim($matches[1]);
        $status = ($actual === $expected) ? '✅' : '❌';
        echo "$status $pattern\n";
        echo "   Expected: '$expected'\n";
        echo "   Actual: '$actual'\n\n";
    }
}

echo "\n" . (count($sinhalaMatches[0]) === 0 && count($tamilMatches[0]) === 0 ? '✅ All checks passed!' : '⚠️  Issues found - hardcoded text should use English defaults') . "\n";
