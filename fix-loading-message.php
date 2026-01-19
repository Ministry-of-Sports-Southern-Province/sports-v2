<?php
$file = 'assets/js/dashboard.js';
$content = file_get_contents($file);

// Fix loading message - replace Sinhala with English
$content = str_replace(
    'data-i18n="message.loading">පූරණය වෙමින්...</span>',
    'data-i18n="message.loading">Loading...</span>',
    $content
);

// Verify the fix
if (strpos($content, 'data-i18n="message.loading">Loading...</span>') !== false) {
    file_put_contents($file, $content);
    echo "✅ Fixed loading message in dashboard.js\n";
} else {
    echo "❌ Could not find or fix loading message\n";
}

// Verify all default texts are English now
echo "\n--- Verification ---\n";
$checks = [
    'Loading...' => strpos($content, 'data-i18n="message.loading">Loading...</span>'),
    'No data available' => strpos($content, 'data-i18n="table.no_data">No data available</span>'),
    'View Details' => strpos($content, 'data-i18n="button.view_details">View Details</a>')
];

foreach ($checks as $text => $found) {
    echo ($found !== false ? '✅' : '❌') . " $text\n";
}
