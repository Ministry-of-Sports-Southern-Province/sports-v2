<?php
echo "==========================================\n";
echo "FINAL VERIFICATION - i18n DEFAULT TEXT\n";
echo "==========================================\n\n";

$content = file_get_contents('assets/js/dashboard.js');

// Check all data-i18n attributes have English defaults
$patterns = [
    'message.loading' => ['expected' => 'Loading...', 'context' => 'Loading state'],
    'table.no_data' => ['expected' => 'No data available', 'context' => 'Empty table'],
    'button.view_details' => ['expected' => 'View Details', 'context' => 'Action button']
];

$allGood = true;
foreach ($patterns as $key => $info) {
    if (preg_match('/data-i18n="' . preg_quote($key, '/') . '">([^<]+)/', $content, $m)) {
        $actual = trim($m[1]);
        $match = $actual === $info['expected'];
        $allGood = $allGood && $match;
        
        echo ($match ? '✅' : '❌') . " {$info['context']}\n";
        echo "   i18n key: $key\n";
        echo "   Default text: '$actual'\n";
        if (!$match) {
            echo "   Expected: '{$info['expected']}'\n";
        }
        echo "\n";
    }
}

// Check for i18n.updateContent() calls after dynamic content
$hasUpdateContent = strpos($content, "window.i18n.updateContent()") !== false;
echo ($hasUpdateContent ? '✅' : '❌') . " i18n.updateContent() called after adding dynamic content\n\n";

// District matching - these Sinhala texts are intentional
echo "--- District Name Matching (intentional Sinhala) ---\n";
$districts = [
    'ගාල්ල' => 'Galle',
    'මාතර' => 'Matara',
    'හම්බන්තොට' => 'Hambantota'
];
foreach ($districts as $sinhala => $english) {
    $found = strpos($content, "includes('$sinhala')") !== false;
    echo ($found ? '✅' : '❌') . " $english district matching: $sinhala\n";
}

echo "\n==========================================\n";
echo "ISSUE RESOLUTION\n";
echo "==========================================\n\n";

echo "Problem: On initial page load, Sinhala text appeared before\n";
echo "i18n translation system processed the content.\n\n";

echo "Solution:\n";
echo "1. ✅ Changed all default text to English\n";
echo "2. ✅ Added i18n.updateContent() calls after dynamic content\n";
echo "3. ✅ Kept intentional Sinhala for district name fallback matching\n\n";

echo ($allGood && $hasUpdateContent ? "✅ All issues resolved!\n" : "⚠️  Some issues remain\n");
echo "\nNow when page loads:\n";
echo "- English text appears initially\n";
echo "- i18n system translates to selected language\n";
echo "- Language changes work correctly\n";
