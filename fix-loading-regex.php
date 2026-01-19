<?php
$content = file_get_contents('assets/js/dashboard.js');
if (preg_match('/data-i18n="message\.loading">([^<]+)/', $content, $m)) {
    echo "Current loading text: " . $m[1] . "\n";
    echo "Length: " . strlen($m[1]) . " bytes\n";
    
    // Replace it
    $newContent = preg_replace(
        '/data-i18n="message\.loading">[^<]+/',
        'data-i18n="message.loading">Loading...',
        $content
    );
    
    file_put_contents('assets/js/dashboard.js', $newContent);
    echo "\n✅ Replaced loading message with English default\n";
} else {
    echo "Could not find loading message pattern\n";
}
